<?php

namespace HollyIT\StaticLibraries\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class StaticResponse implements Responsable
{
    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public static function fromFile($file): CssResponse|JsResponse
    {
        if (Str::endsWith($file, '.js')) {
            return new JsResponse($file);
        }

        if (Str::endsWith($file, '.css')) {
            return new CssResponse($file);
        }
        abort(404);
    }

    protected function getHttpDate(int $timestamp): string
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }

    protected function pretendResponseIsFile(string $path, string $contentType): \Illuminate\Http\Response|BinaryFileResponse
    {
        abort_unless(
            file_exists($path) || file_exists($path = base_path($path)),
            404,
        );
        $cacheControl = 'public, max-age=31536000';
        $expires = strtotime('+1 year');

        $lastModified = filemtime($path);

        if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') === $lastModified) {
            return response()->noContent(304, [
                'Expires' => $this->getHttpDate($expires),
                'Cache-Control' => $cacheControl,
            ]);
        }

        return response()->file($path, [
            'Content-Type' => $contentType,
            'Expires' => $this->getHttpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => $this->getHttpDate($lastModified),
        ]);
    }

    abstract public function handle($request): Response;

    public function toResponse($request): Response
    {
        return $this->handle($request);
    }
}
