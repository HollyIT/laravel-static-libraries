<?php

namespace HollyIT\StaticLibraries\StorageDrivers;

use HollyIT\StaticLibraries\Library;

class DevDriver extends AbstractDriver
{
    public function url(Library $library, string $file): string
    {
        if ($hotFile = $library->getHotFile()) {
            if (file_exists($hotFile)) {
                return trim(file_get_contents($hotFile)) . '/' . $file;
            }

        }
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

}
