<?php

namespace HollyIT\StaticLibraries\StaticAssets;

use HollyIT\StaticLibraries\Library;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

abstract class StaticAsset implements Arrayable
{
    protected array $attributes = [];

    protected Library $library;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getType(): string
    {
        return Str::lcfirst(class_basename(static::class));
    }

    /**
     * @return Library
     */
    public function getLibrary(): Library
    {
        return $this->library;
    }

    /**
     * @param  Library  $library
     * @return static
     */
    public function setLibrary(Library $library): static
    {
        $this->library = $library;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param  array  $attributes
     * @return static
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function withAttribute(string $name, mixed $value = true): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function attributesToString(): string
    {
        $results = [];
        foreach ($this->attributes as $name => $value) {
            if (is_bool($value)) {
                $results[] = $name;
            } elseif (is_int($name)) {
                $results[] = $value;
            } else {
                $results[] = $name.'="'.htmlspecialchars($value).'"';
            }
        }

        return (! empty($results) ? ' ' : '').implode(' ', $results);
    }

    abstract public function render(): string;

    public function __toString(): string
    {
        return $this->render();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
