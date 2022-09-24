<?php

namespace HollyIT\StaticLibraries\StorageDrivers\Concerns;

use HollyIT\StaticLibraries\Library;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait DriverOffersCacheBusting
{
    protected string $manifestCacheName = 'static_assets_manifest';

    protected string $urlCacheKey = '_c';

    protected int|string|null $manifest_ttl = null;

    protected ?array $manifest = [];

    protected function cacheBust(Library $library, string $url, $file): string
    {
        $key = $this->getManifestCacheKey($library, $file);
        if (! $key) {
            return $url;
        }
        echo "$key ";
        return $url.(str_contains($url, '?') ? '&' : '?').
            $this->urlCacheKey.'='.substr(md5($key), 0, 12);
    }

    public function getLibraryManifest(Library $library): ?array
    {
        if (! $this->manifest) {
            $this->manifest = Cache::get($this->manifestCacheName, []);
        }

        return $this->manifest[$library->getName()] ?? null;
    }

    public function getManifestFile(Library $library, string $file = null): ?string
    {
        if (! $this->manifest) {
            $this->manifest = Cache::get($this->manifestCacheName, []);
        }

        $library = $this->getLibraryManifest($library);

        if (! $library) {
            return null;
        }

        return $library[$file] ?? null;
    }

    public function cacheManifestItem(Library $library, string $file, $cacheKey = null): void
    {
        $cacheKey = $cacheKey ?? $this->makeCacheKey($file, $library);
        if (! $this->getLibraryManifest($library)) {
            $this->manifest[$library->getName()] = [];
        }
        $this->manifest[$library->getName()][$file] = $cacheKey;
        $this->persistManifestCache();
    }

    public function forgetManifestLibrary(Library $library): void
    {
        if ($this->getLibraryManifest($library)) {
            Arr::forget($this->manifest, $library->getName());
            $this->persistManifestCache();
        }
    }

    public function getManifestCacheKey(Library $library, string $file, bool $add = true): string|null
    {
        if ($add && ! $this->getManifestFile($library, $file)) {
            $this->cacheManifestItem($library, $file, $this->makeCacheKey($file, $library));
        }

        return $this->getManifestFile($library, $file);
    }

    public function makeCacheKey(string $path, ?Library $library = null): bool|string
    {
        $absPath = $library ? $library->filePath($path) : $path;
        return File::exists($absPath) ? hash_file('md5', $absPath) : 0;
    }

    protected function persistManifestCache(): void
    {
        $ttl = $this->manifest_ttl;
        $ttl = is_string($ttl) ? strtotime($ttl) : $ttl;
        Cache::put($this->manifestCacheName, $this->manifest, $ttl === 0 ? null : $ttl);
    }
}
