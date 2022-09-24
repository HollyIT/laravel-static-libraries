<?php

namespace HollyIT\StaticLibraries\StorageDrivers;

use HollyIT\StaticLibraries\Contracts\PublishesStaticAssets;
use HollyIT\StaticLibraries\Library;
use HollyIT\StaticLibraries\StorageDrivers\Concerns\DriverOffersCacheBusting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class FileSystemDriver extends AbstractDriver implements PublishesStaticAssets
{
    use DriverOffersCacheBusting;

    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options['publish_path'] = Config::get('static-libraries.server_prefix');
        $this->manifest_ttl = $this->getOption('manifest_cache_ttl', null);
        $this->urlCacheKey = $this->getOption('cache_busting_key', '_c');
    }

    public function url(Library $library, string $file): string
    {
        return $this->cacheBust($library, parent::url($library, $file), $file);
    }

    public function publish(Library $library, bool $force = false): bool|string
    {
        if ($library->isInPublic()) {
            return 'skipped public path library';
        }

        $staticPath = public_path($this->getOption('publish_path', 'static').'/'.$library->getName());

        File::ensureDirectoryExists($staticPath);
        foreach ($library->allFiles() as $file) {
            $sourcePath = $library->filePath($file);
            if (file_exists($sourcePath)) {
                $destination = $staticPath.'/'.$file;
                if ($this->fileIsPublished($library, $file)) {
                    if (! $force && ! $this->fileIsStale($library, $file)) {
                        break;
                    }

                    File::delete($destination);
                }
                $basePath = pathinfo($destination, PATHINFO_DIRNAME);
                if ($basePath !== $staticPath && ! File::isDirectory($basePath)) {
                    File::ensureDirectoryExists($basePath);
                }

                File::copy($sourcePath, $destination);
                $this->cacheManifestItem($library, $file);
            }
        }

        return true;
    }

    public function unpublish(Library $library): bool|string
    {
        if ($library->isInPublic()) {
            return 'skipped public path library';
        }
        $staticPath = public_path($this->getOption('publish_path', 'static').'/'.$library->getName());
        if (File::isDirectory($staticPath)) {
            File::deleteDirectory($staticPath);
        }
        $this->forgetManifestLibrary($library);

        return true;
    }

    public function fileIsPublished(Library $library, string $file): bool
    {
        $staticPath = public_path($this->getOption('publish_path', 'static').'/'.$library->getName());

        return File::exists($staticPath.'/'.$file);
    }

    public function fileIsStale(Library $library, string $file): bool
    {
        $sourcePath = $library->filePath($file);
        $publishedPath = public_path($this->getOption('publish_path', 'static').'/'.$library->getName()).'/'.$file;
        $savedCache = $this->getManifestCacheKey($library, $file, false);

        return $this->fileIsPublished($library, $file) && $savedCache != $this->makeCacheKey($file, $library);
    }
}
