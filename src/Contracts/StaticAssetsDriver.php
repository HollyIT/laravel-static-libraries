<?php

namespace HollyIT\StaticLibraries\Contracts;

use HollyIT\StaticLibraries\Library;
use HollyIT\StaticLibraries\Responses\StaticResponse;

interface StaticAssetsDriver
{
    public function url(Library $library, string $file): string;

    public function serve(Library $library, string $staticFile): StaticResponse;
}
