<?php

namespace HollyIT\StaticLibraries\StaticAssets;

use HollyIT\StaticLibraries\Contracts\IsStyle;

class Css extends StaticFile implements IsStyle
{
    public static function make(string $file, array $attributes = []): Css
    {
        return new Css($file, $attributes);
    }

    public function render(): string
    {
        $this->attributes = array_merge([
            'href' => $this->resolveFileUrl(),
            'rel' => 'stylesheet',
        ], $this->attributes);

        return '<link '.$this->attributesToString().'>';
    }
}
