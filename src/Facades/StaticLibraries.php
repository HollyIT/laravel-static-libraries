<?php

namespace HollyIT\StaticLibraries\Facades;

use HollyIT\StaticLibraries\LibrariesManager;
use Illuminate\Support\Facades\Facade;

class StaticLibraries extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LibrariesManager::class;
    }
}
