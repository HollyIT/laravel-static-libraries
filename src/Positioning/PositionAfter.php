<?php

namespace HollyIT\StaticLibraries\Positioning;

use Closure;
use HollyIT\StaticLibraries\Concerns\EvaluatesCallbacks;
use HollyIT\StaticLibraries\Resolvers\ResolvedLibrary;
use Illuminate\Support\Collection;

class PositionAfter extends PositioningRule
{
    use EvaluatesCallbacks;

    protected Closure|array $libraries;

    public function __construct(array|Closure|string $libraries)
    {
        $this->setLibraries($libraries);
    }

    /**
     * @return array|Closure
     */
    public function getLibraries(): array|Closure
    {
        return $this->evaluateCallback($this->libraries, $this);
    }

    /**
     * @param  array|Closure|string  $libraries
     * @return PositionAfter
     */
    public function setLibraries(array|Closure|string $libraries): PositionAfter
    {
        if (is_string($libraries)) {
            $libraries = [$libraries];
        }
        $this->libraries = $libraries;

        return $this;
    }

    public function handle(ResolvedLibrary $library, Collection $required): void
    {
        foreach ($this->libraries as $libraryName) {
            if (isset($required[$libraryName]) && $required[$libraryName]->getWeight() > $library->getWeight()) {
                $library->setWeight($required[$libraryName]->getWeight() + 1);
            }
        }
    }

    public function toArray(): array
    {
        return [
            'position' => 'after',
            'libraries' => $this->getLibraries(),
        ];
    }
}
