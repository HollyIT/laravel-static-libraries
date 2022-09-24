<?php

namespace HollyIT\StaticLibraries\Positioning;

use HollyIT\StaticLibraries\Resolvers\ResolvedLibrary;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

abstract class PositioningRule implements Arrayable
{
    /**
     * @param ResolvedLibrary $library
     * @param Collection|ResolvedLibrary[] $required
     * @return void
     */
    abstract public function handle(ResolvedLibrary $library, Collection $required): void;

    abstract public function toArray(): array;
}
