<?php

namespace HollyIT\StaticLibraries\Resolvers;

use HollyIT\StaticLibraries\LibrariesManager;

class RequiredLibraryResolver
{
    protected LibrariesManager $manager;

    protected array $required;

    /** @var array | ResolvedLibrary[] */
    protected array $resolvedLibraries = [];

    protected bool $isOrdered = false;

    protected array $autoInjects;

    public function __construct(array $required, LibrariesManager $manager)
    {
        $this->manager = $manager;
        $this->required = $required;
        $this->resolve();
    }

    protected function resolve(): void
    {
        // So we aren't constantly looping over every library, we'll go
        // ahead and determine what libraries auto-inject ($library->requiredWith()).
        $this->autoInjects = [];
        foreach ($this->manager->all() as $library) {
            foreach ($library->getRequiredWith() as $libraryName) {
                if (! isset($this->autoInjects[$libraryName])) {
                    $this->autoInjects[$libraryName] = [];
                }
                $this->autoInjects[$libraryName][] = $library->getName();
            }
        }

        foreach ($this->required as $libraryName) {
            throw_if(! $this->manager->has($libraryName), new \Exception('Could not locate required static library '.$libraryName));
            $this->resolveDependencies($libraryName);
        }

        foreach ($this->resolvedLibraries as $resolvedLibrary) {
            if ($positionRule = $resolvedLibrary->library->getPositionRule()) {
                $positionRule->handle($resolvedLibrary, $this->resolvedLibraries);
            }
        }
    }

    protected function resolveDependencies(string $libraryName): void
    {
        throw_if(! $this->manager->has($libraryName), sprintf('Required static library %s could not be located', $libraryName));
        $library = $this->manager->get($libraryName);
        foreach ($library->getDependencies() as $dependencyName) {
            throw_if($dependencyName === $library, sprintf('Library %s is attempting to require itself.', $libraryName));
            if (! isset($this->resolvedLibraries[$dependencyName])) {
                $this->resolveDependencies($dependencyName);
            }
        }

        if (! isset($this->resolvedLibraries[$libraryName])) {
            $this->resolvedLibraries[$libraryName] = new ResolvedLibrary($this, $library, count($this->resolvedLibraries) * 10);
            if (isset($this->autoInjects[$libraryName])) {
                foreach ($this->autoInjects[$libraryName] as $injectName) {
                    $this->resolveDependencies($injectName);
                }
            }
        }
    }

    public function needsOrdered(): static
    {
        $this->isOrdered = false;

        return $this;
    }

    protected function order(): void
    {
        uasort($this->resolvedLibraries, fn (ResolvedLibrary $a, ResolvedLibrary $b) => $a->getWeight() < $b->getWeight() ? -1 : 1);
        $this->isOrdered = true;
    }

    public function toArray(): array
    {
        if (! $this->isOrdered) {
            $this->order();
        }

        return $this->resolvedLibraries;
    }

    public function getLibraries(): \Illuminate\Support\Collection
    {
        return collect($this->toArray())->map(fn (ResolvedLibrary $library) => $library->library);
    }
}
