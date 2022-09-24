<?php

namespace HollyIT\StaticLibraries\Positioning;

use HollyIT\StaticLibraries\Resolvers\ResolvedLibrary;
use Illuminate\Contracts\Support\Arrayable;

abstract class PositioningRule implements Arrayable
{
    /**
     * @param  ResolvedLibrary  $library
     * @param  array | ResolvedLibrary[]  $required
     * @return void
     */
    abstract public function handle(ResolvedLibrary $library, array $required): void;

    abstract public function toArray(): array;
}
