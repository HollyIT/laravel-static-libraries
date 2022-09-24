<?php

namespace HollyIT\StaticLibraries\Contracts;

interface IsScript
{
    public function inHead(bool $head = true): static;

    public function isInHead(): bool;
}
