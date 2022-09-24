<?php

namespace HollyIT\StaticLibraries\StaticAssets;

abstract class StaticFile extends StaticAsset
{
    protected string $file = '';

    public function __construct(string $file, array $attributes = [])
    {
        parent::__construct($attributes);
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param  string  $file
     * @return static
     */
    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function isExternal(): bool
    {
        return str_contains($this->file, '://');
    }

    public function resolveFileUrl(): string
    {
        return $this->isExternal() ? $this->file : $this->getLibrary()->librariesManager()->libraryFileToUrl($this->library, $this->file);
    }

    public function absolutePath(): string
    {
        return $this->library->getBasePath().'/'.$this->getFile();
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['url' => $this->resolveFileUrl()]);
    }
}
