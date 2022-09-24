<?php

namespace HollyIT\StaticLibraries\StaticAssets;

use HollyIT\StaticLibraries\Contracts\IsScript;
use HollyIT\StaticLibraries\StaticAssets\Concerns\CanBeInHead;

class Js extends StaticFile implements IsScript
{
    use CanBeInHead;

    public static function make(string $file, array $attributes = []): Js
    {
        return new Js($file, $attributes);
    }

    public function render(): string
    {
        $this->attributes = array_merge(['src' => $this->resolveFileUrl()], $this->attributes);

        return '<script'.$this->attributesToString().'></script>';
    }
}
