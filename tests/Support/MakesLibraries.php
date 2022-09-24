<?php

namespace HollyIT\StaticLibraries\Test\Support;

use HollyIT\StaticLibraries\Library;
use HollyIT\StaticLibraries\StaticAssets\Css;
use HollyIT\StaticLibraries\StaticAssets\Js;
use HollyIT\StaticLibraries\StaticAssets\StaticAsset;
use Illuminate\Support\Facades\File;

class MakesLibraries
{
    public static function basic($name): Library
    {
        return Library::make($name, __DIR__.'/../temp/'.$name)
            ->assets(
                Css::make($name.'.css'),
                Js::make($name.'.js')
            );
    }

    /**
     * @param $name
     * @param  array|StaticAsset[]  $assets
     * @return Library
     */
    public static function withAssets($name, array $assets): Library
    {
        $basePath = __DIR__.'/../temp/'.$name;
        $library = Library::make($name, $basePath)->assets(...$assets);
        foreach ($assets as $asset) {
            File::ensureDirectoryExists(pathinfo($asset->absolutePath(), PATHINFO_DIRNAME));
            File::put($asset->absolutePath(), $asset->getFile());
        }

        return $library;
    }
}
