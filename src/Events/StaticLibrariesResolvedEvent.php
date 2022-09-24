<?php

namespace HollyIT\StaticLibraries\Events;

use HollyIT\StaticLibraries\Resolvers\RequiredLibraryResolver;

class StaticLibrariesResolvedEvent
{

    public RequiredLibraryResolver $resolver;

    public function __construct(RequiredLibraryResolver $resolver)
    {
        $this->resolver = $resolver;
    }
}
