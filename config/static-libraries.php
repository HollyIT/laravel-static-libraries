<?php

use HollyIT\StaticLibraries\StorageDrivers\DevDriver;
use HollyIT\StaticLibraries\StorageDrivers\FileSystemDriver;

return [
    'driver' => env('STATIC_LIBRARIES_DRIVER', 'filesystem'),
    'server_prefix' => 'static',
    'drivers' => [
        'filesystem' => [
            'driver' => FileSystemDriver::class,
            'options' => [
                'fallback_route' => true,
                'fallback_middleware' => [],
                'cache_busting_key' => '_c',
                'manifest_cache_ttl' => 0, // set to 0 for infinite or pass an int for number of seconds or strtotime string

            ],
        ],

        'dev' => [
            'driver' => DevDriver::class,
        ],
    ],
    'import_map_shim' => '<script async src="https://ga.jspm.io/npm:es-module-shims@1.5.18/dist/es-module-shims.js"></script>',
];
