<?php

namespace HollyIT\StaticLibraries\StaticAssets;

use Closure;
use HollyIT\StaticLibraries\Concerns\EvaluatesCallbacks;
use HollyIT\StaticLibraries\Contracts\IsScript;
use HollyIT\StaticLibraries\StaticAssets\Concerns\CanBeInHead;

class InlineScript extends StaticAsset implements IsScript
{
    use EvaluatesCallbacks, CanBeInHead;

    protected Closure|string $contents;

    public function __construct(string | Closure $contents, array $attributes = [])
    {
        parent::__construct($attributes);
        $this->contents = $contents;
    }

    public static function make(string | Closure $contents, array $attributes = []): InlineScript
    {
        return new InlineScript($contents, $attributes);
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->evaluateCallback($this->contents, $this);
    }

    /**
     * @param  Closure|string  $contents
     * @return static
     */
    public function setContents(Closure|string $contents): static
    {
        $this->contents = $contents;

        return $this;
    }

    public function render(): string
    {
        return '<script'.$this->attributesToString().'>'.$this->getContents().'</script>';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['content' => $this->getContents()]);
    }
}
