<?php

namespace HollyIT\StaticLibraries\Contracts;

use HollyIT\StaticLibraries\Library;

interface PublishesStaticAssets
{
    public function publish(Library $library, bool $force = false);

    public function unpublish(Library $library);

    public function fileIsPublished(Library $library, string $file): bool;

    public function fileIsStale(Library $library, string $file): bool;
}
