<?php

namespace HollyIT\StaticLibraries;

use Closure;
use HollyIT\StaticLibraries\Concerns\EvaluatesCallbacks;
use HollyIT\StaticLibraries\Positioning\PositioningRule;
use HollyIT\StaticLibraries\StaticAssets\JsModule;
use HollyIT\StaticLibraries\StaticAssets\StaticAsset;
use HollyIT\StaticLibraries\StaticAssets\StaticFile;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Library implements Arrayable
{
    use EvaluatesCallbacks;

    protected string $name;

    protected array|Closure $dependencies = [];

    protected array|Closure $requiredWith = [];

    protected ?PositioningRule $positionRule = null;

    protected array $positioningItems = [];

    /**
     * @var Collection | JsModule[] | StaticAsset[]
     */
    public Collection|array $assets;

    protected array|Closure $data = [];

    protected string $basePath;

    protected ?LibrariesManager $librariesManager = null;

    protected bool $isInPublic = false;

    public function __construct(string $name, string $basePath)
    {
        $this->name = $name;
        $this->setBasePath($basePath);
        $this->assets = collect([]);
    }

    /**
     * Allows the library to interact with the library manager that registered it.
     *
     * @param  LibrariesManager|null  $librariesManager
     */
    public function setLibrariesManager(?LibrariesManager $librariesManager): void
    {
        $this->librariesManager = $librariesManager;
    }

    public static function make(string $name, string $basePath = ''): Library
    {
        return new Library($name, $basePath);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param  string  $basePath
     * @return static
     */
    public function setBasePath(string $basePath): static
    {
        $this->basePath = rtrim(empty($basePath) ? public_path() : $basePath, '/');
        $this->isInPublic = str_starts_with(strtolower(realpath($this->basePath)), strtolower(realpath(public_path())));

        return $this;
    }

    /**
     * Indicates if this library is actually stored in the public_path(). This prevents
     * any publishing from occurring and possibly wiping out the files.
     *
     * @return bool
     */
    public function isInPublic(): bool
    {
        return $this->isInPublic;
    }

    /**
     * List the dependencies of this library.
     *
     * @param  string|array|Closure  $dependencies
     * @return static
     */
    public function requires(string|array|Closure $dependencies): static
    {
        return $this->mergeTo($dependencies, 'dependencies');
    }

    /**+
     * Insert this library whenever the defined libraries are required.
     *
     * @param string|array|Closure $libraries
     * @return $this
     */
    public function requiredWith(string|array|Closure $libraries): static
    {
        return $this->mergeTo($libraries, 'requiredWith');
    }

    /**
     * @return PositioningRule|null
     */
    public function getPositionRule(): ?PositioningRule
    {
        return $this->positionRule;
    }

    /**
     * @param  PositioningRule|null  $positionRule
     * @return Library
     */
    public function position(?PositioningRule $positionRule): Library
    {
        $this->positionRule = $positionRule;

        return $this;
    }

    protected function mergeTo(array|string|Closure $items, string $collection): static
    {
        if ($items instanceof Closure) {
            $this->{$collection} = $items;
        } else {
            $items = is_array($items) ? $items : [$items];
            collect($items)->each(function ($item) use ($collection) {
                if (! in_array($item, $this->{$collection})) {
                    $this->{$collection}[] = $item;
                }
            });
        }

        return $this;
    }

    /**
     * A list of StaticAsset to add to this library.
     *
     * @param ...$assets
     * @return $this
     */
    public function assets(...$assets): static
    {
        collect($assets)->each(fn (StaticAsset $asset) => $asset->setLibrary($this));
        $this->assets->push(...$assets);

        return $this;
    }

    /**
     * Javascript data that should be injected with this library.
     *
     * @param  array|Closure  $data
     * @return $this
     */
    public function withData(array|Closure $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Add an item to this libraries. data. $Key can be specified
     * using dot notation.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function addData(string $key, mixed $value): static
    {
        Arr::set($this->data, $key, $value);

        return $this;
    }

    /**
     * Remove a data item from this library. $Key may utilize dot notation.
     *
     * @param  string  $key
     * @return $this
     */
    public function removeDataItem(string $key): static
    {
        Arr::forget($this->data, $key);

        return $this;
    }

    protected function _mapRecursive(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->_mapRecursive($value);
            } else {
                $array[$key] = $this->evaluateCallback($value, $this);
            }
        }

        return $array;
    }

    /**
     * Returns an array of the javascript data required for this library.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->_mapRecursive($this->data);
    }

    /**
     * Return an array of the libraries this library requires.
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->evaluateCallback($this->dependencies, $this);
    }

    /**
     * @return array
     */
    public function getRequiredWith(): array
    {
        return $this->evaluateCallback($this->requiredWith, $this);
    }

    /**
     * @return LibrariesManager|null
     */
    public function librariesManager(): ?LibrariesManager
    {
        return $this->librariesManager;
    }

    public function filePath($file): string
    {
        return $this->basePath.'/'.$file;
    }

    public function allFiles(): array
    {
        return $this->assets
            ->filter(fn (StaticAsset $asset) => $asset instanceof StaticFile)
            ->map(fn (StaticFile $asset) => $asset->getFile())->toArray();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'depends_on' => $this->getDependencies(),
            'required_with' => $this->getRequiredWith(),
            'positioning' => $this->positionRule?->toArray(),
            'data' => $this->getData(),
            'assets' => $this->assets->toArray(),
        ];
    }
}
