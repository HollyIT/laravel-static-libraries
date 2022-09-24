<?php

namespace HollyIT\StaticLibraries\Responses;

use Symfony\Component\HttpFoundation\Response;

class JsResponse extends StaticResponse
{
    public function handle($request): Response
    {
        return $this->pretendResponseIsFile($this->file, 'application/javascript; charset=utf-8');
    }
}
