<?php

namespace HollyIT\StaticLibraries\Events;

use HollyIT\StaticLibraries\LibrariesManager;

class PrepareStaticLibraryOrder
{
    public LibrariesManager $manager;

    public function __construct(LibrariesManager $manager)
    {
        $this->manager = $manager;
    }
}
