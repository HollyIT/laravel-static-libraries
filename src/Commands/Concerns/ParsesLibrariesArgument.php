<?php

namespace HollyIT\StaticLibraries\Commands\Concerns;

use HollyIT\StaticLibraries\Library;
use Illuminate\Support\Collection;

trait ParsesLibrariesArgument
{
    /**
     * @return Collection | Library[]
     */
    protected function getLibrariesFromInput(): Collection
    {
        $intent = $this->argument('library');
        if ($intent) {
            $libraries = collect(explode(',', $intent))
                ->map(function ($libraryName) {
                    if (trim($libraryName)) {
                        return $this->librariesManager->get($libraryName);
                    }

                    return null;
                })->filter();
        } else {
            $libraries = $this->librariesManager->libraries;
        }

        return $libraries;
    }
}
