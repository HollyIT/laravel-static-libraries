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
        $time = $this->getManifestCacheTime($library, $file);
        if (! $time) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').
            $this->urlCacheKey.'='.substr(md5($time), 0, 12);
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

    public function cacheManifestItem(Library $library, string $file, $mTime): void
    {
        if (! $this->getLibraryManifest($library)) {
            $this->manifest[$library->getName()] = [];
        }
        $this->manifest[$library->getName()][$file] = $mTime;
        $this->persistManifestCache();
    }

    public function forgetManifestLibrary(Library $library): void
    {
        if ($this->getLibraryManifest($library)) {
            Arr::forget($this->manifest, $library->getName());
            $this->persistManifestCache();
        }
    }

    public function getManifestCacheTime(Library $library, string $file, bool $add = true): string|null
    {
        if ($add && ! $this->getManifestFile($library, $file)) {
            $this->cacheManifestItem($library, $file, $this->makeCacheKey($library, $file));
        }

        return $this->getManifestFile($library, $file);
    }

    public function makeCacheKey(Library $library, string $file): bool|int
    {
        $absPath = $library->filePath($file);

        return File::exists($absPath) ? filemtime($absPath) : 0;
    }

    protected function persistManifestCache(): void
    {
        $ttl = $this->manifest_ttl;
        $ttl = is_string($ttl) ? strtotime($ttl) : $ttl;
        Cache::put($this->manifestCacheName, $this->manifest, $ttl === 0 ? null : $ttl);
    }
}
