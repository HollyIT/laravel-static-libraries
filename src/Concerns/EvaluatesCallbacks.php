<?php

namespace HollyIT\StaticLibraries\Concerns;

use Closure;

trait EvaluatesCallbacks
{
    public function evaluateCallback($callable, ...$params)
    {
        return $callable instanceof Closure ? app()->call($callable, $params) : $callable;
    }
}
