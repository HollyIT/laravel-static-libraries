<?php

namespace HollyIT\StaticLibraries\Resolvers;

use HollyIT\StaticLibraries\Library;

class ResolvedLibrary
{
    public Library $library;

    protected int $weight;

    protected RequiredLibraryResolver $resolver;

    public function __construct(RequiredLibraryResolver $resolver, Library $library, int $weight)
    {
        $this->library = $library;
        $this->weight = $weight;
        $this->resolver = $resolver;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param  int  $weight
     * @return ResolvedLibrary
     */
    public function setWeight(int $weight): ResolvedLibrary
    {
        $this->weight = $weight;
        $this->resolver->needsOrdered();

        return $this;
    }
}
