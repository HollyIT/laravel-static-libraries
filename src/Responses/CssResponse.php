<?php

namespace HollyIT\StaticLibraries\Responses;

use Symfony\Component\HttpFoundation\Response;

class CssResponse extends StaticResponse
{
    public function handle($request): Response
    {
        return $this->pretendResponseIsFile($this->file, 'text/css; charset=utf-8');
    }
}
