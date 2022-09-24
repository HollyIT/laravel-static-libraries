<?php

namespace HollyIT\StaticLibraries\StaticAssets\Concerns;

trait CanBeInHead
{
    protected bool $head = false;

    public function inHead(bool $head = true): static
    {
        $this->head = $head;

        return $this;
    }

    public function isInHead(): bool
    {
        return $this->head;
    }
}
