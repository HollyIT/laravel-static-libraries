<?php

namespace HollyIT\StaticLibraries;

use HollyIT\StaticLibraries\Contracts\IsScript;
use HollyIT\StaticLibraries\Contracts\IsStyle;
use HollyIT\StaticLibraries\Contracts\PublishesStaticAssets;
use HollyIT\StaticLibraries\Contracts\StaticAssetsDriver;
use HollyIT\StaticLibraries\Events\PrepareStaticLibraryOrder;
use HollyIT\StaticLibraries\Events\RegisterStaticLibrariesEvent;
use HollyIT\StaticLibraries\Resolvers\RequiredLibraryResolver;
use HollyIT\StaticLibraries\StaticAssets\JsModule;
use HollyIT\StaticLibraries\StaticAssets\StaticAsset;
use HollyIT\StaticLibraries\StaticAssets\StaticFile;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use RuntimeException;

class LibrariesManager
{
    /**
     * @var Collection | Library[]
     */
    public Collection|array $libraries;

    protected array $required = [];

    protected array $data = [];

    /**
     * @var Collection|Library[]|null
     */
    protected Collection|array|null $ordered = null;

    protected StaticAssetsDriver $assetsDriver;

    public function __construct(StaticAssetsDriver $assetsDriver)
    {
        $this->libraries = collect([]);
        $this->assetsDriver = $assetsDriver;
        $this->discover();
    }

    protected function discover(): void
    {
        event(new RegisterStaticLibrariesEvent($this));
    }

    public function driver(): StaticAssetsDriver
    {
        return $this->assetsDriver;
    }

    public function add(Library $library): static
    {
        $this->libraries->put($library->getName(), $library);
        $library->setLibrariesManager($this);

        return $this;
    }

    public function get(string $name): Library
    {
        return $this->libraries->get($name);
    }

    public function has(string $name): bool
    {
        return $this->libraries->has($name);
    }

    /**
     * @return Library[]|Collection
     */
    public function all(): array|Collection
    {
        return $this->libraries;
    }

    public function require(...$name): static
    {
        foreach ($name as $library) {
            if (is_array($library)) {
                $this->require(...$library);
            } elseif (! $this->isRequired($library)) {
                if (! $this->has($library)) {
                    throw new RuntimeException('Unknown static library '.$library);
                }
                $this->ordered = null;
                $this->required[] = $library;
            }
        }

        return $this;
    }

    public function isRequired(string $name): bool
    {
        return in_array($name, $this->required);
    }

    public function removeRequired(string $name): static
    {
        if (($key = array_search($name, $this->required)) !== false) {
            unset($this->required[$key]);
        }
        $this->ordered = null;

        return $this;
    }

    public function createResolver(): RequiredLibraryResolver
    {
        return new RequiredLibraryResolver($this->required, $this);
    }

    /**
     * @return Library[]|Collection|null
     */
    public function getOrdered(): array|Collection|null
    {
        if (! $this->ordered) {
            event(new PrepareStaticLibraryOrder($this));
            $this->ordered = $this->createResolver()->getLibraries();
        }

        return $this->ordered;
    }

    public function getScripts(bool $inHead = false): array
    {
        $scripts = [];
        foreach ($this->getOrdered() as $library) {
            $scripts = array_merge($scripts, $library->assets
                ->filter(fn (StaticAsset $asset) => ($asset instanceof IsScript) && $asset->isInHead() === $inHead)
                ->map(fn (IsScript|StaticAsset $js) => $js->render())
                ->toArray()
            );
        }

        return $scripts;
    }

    public function getStyleSheets(): array
    {
        $styles = [];
        foreach ($this->getOrdered() as $library) {
            $styles = array_merge($styles, $library->assets
                ->filter(fn (StaticAsset $asset) => $asset instanceof IsStyle)
                ->map(fn (IsStyle|StaticAsset $css) => $css->render())
                ->toArray()
            );
        }

        return $styles;
    }

    public function withData($key, $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->getOrdered() as $library) {
            $data = array_replace_recursive($data, $library->getData());
        }

        return array_replace_recursive($data, $this->data);
    }

    public function libraryFileToUrl(Library $library, string $path): string
    {
        return $this->driver()->url($library, $path);
    }

    public function renderHead(): Factory|View|Application
    {
        return view('static-libraries::render_head', [
            'libraries' => $this,
        ]);
    }

    public function renderFooter(): Factory|View|Application
    {
        return view('static-libraries::render_footer', [
            'libraries' => $this,
        ]);
    }

    public function getModuleMapShim(): string
    {
        return config('static-libraries.import_map_shim', '');
    }

    public function getModuleMap(): array
    {
        $output = '';
        $map = [];
        foreach ($this->getOrdered() as $library) {
            /** @var Collection $found */
            $found = $library->assets
                ->filter(fn (StaticAsset $asset) => $asset instanceof JsModule)
                ->mapWithKeys(fn (JsModule $module) => [$module->getName() => $module->resolveFileUrl()]);

            if ($found->isNotEmpty()) {
                $map = array_merge($map, $found->toArray());
            }
        }

        return $map;
    }

    public function devInfo(): array
    {
        $info = [];
        $driver = $this->driver();
        foreach ($this->all() as $library) {
            $item = $library->toArray();
            $item['base_path'] = $library->getBasePath();
            $item['assets'] = [];
            foreach ($library->assets as $asset) {
                $data = $asset->toArray();
                if ($asset instanceof StaticFile) {
                    $data['file'] = $asset->getFile();

                    if (! $asset->isExternal() && $driver instanceof PublishesStaticAssets) {
                        $data['is_published'] = $driver->fileIsPublished($library, $asset->getFile());
                        $data['is_stale'] = $driver->fileIsStale($library, $asset->getFile());
                    }
                }
                $item['assets'][] = $data;
            }
            $info[] = $item;
        }

        return $info;
    }
}
