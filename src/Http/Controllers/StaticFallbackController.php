<?php

namespace HollyIT\StaticLibraries\Http\Controllers;

use HollyIT\StaticLibraries\LibrariesManager;
use Illuminate\Routing\Controller;

class StaticFallbackController extends Controller
{
    public function __invoke($staticFileLibrary, $staticFile, LibrariesManager $librariesManager)
    {
        abort_if(! $librariesManager->has($staticFileLibrary), 404);
        $library = $librariesManager->get($staticFileLibrary);

        return $librariesManager->driver()->serve($library, $staticFile);
    }
}
