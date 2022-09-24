<?php

namespace HollyIT\StaticLibraries\StorageDrivers;

use HollyIT\StaticLibraries\Contracts\StaticAssetsDriver;
use HollyIT\StaticLibraries\Library;
use HollyIT\StaticLibraries\Responses\StaticResponse;
use Illuminate\Support\Arr;

abstract class AbstractDriver implements StaticAssetsDriver
{
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->options, $key, $default);
    }

    public function url(Library $library, string $file): string
    {
        if ($library->isInPublic()) {
            $url = asset(str_replace(public_path(), '/', $file));
        } else {
            $url = route('static_assets_server', [
                'static_file_library' => $library->getName(),
                'static_file' => $file,
            ]);
        }

        return $url;
    }

    public function serve(Library $library, string $staticFile): StaticResponse
    {
        $file = $library->getBasePath().'/'.$staticFile;
        abort_if(! file_exists($file), 404);

        return StaticResponse::fromFile($file);
    }
}
