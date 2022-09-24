<?php

namespace HollyIT\StaticLibraries\StaticAssets;

use HollyIT\StaticLibraries\Contracts\IsScript;
use HollyIT\StaticLibraries\StaticAssets\Concerns\CanBeInHead;

class JsModule extends StaticFile implements IsScript
{
    use CanBeInHead;

    protected string $name;

    /**
     * @param  string  $file
     * @param  string  $name
     * @param  array  $attributes
     */
    public function __construct(string $file, string $name, array $attributes = [])
    {
        parent::__construct($file, $attributes);
        $this->name = $name;
    }

    public static function make(string $file, string $name, array $attributes = []): JsModule
    {
        return new JsModule($file, $name, $attributes);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     * @return JsModule
     */
    public function setName(string $name): JsModule
    {
        $this->name = $name;

        return $this;
    }

    public function render(): string
    {
        $this->attributes = array_merge([
            'src' => $this->resolveFileUrl(),
            'type' => 'module',
        ], $this->attributes);

        return'<script '.$this->attributesToString().'></script>';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['name' => $this->getName()]);
    }
}
