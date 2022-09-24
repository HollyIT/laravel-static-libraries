<?php

namespace HollyIT\StaticLibraries\Events;

use HollyIT\StaticLibraries\LibrariesManager;

class ResolveStaticLibrariesEvent
{
    public LibrariesManager $manager;

    public function __construct(LibrariesManager $manager)
    {
        $this->manager = $manager;
    }
}
