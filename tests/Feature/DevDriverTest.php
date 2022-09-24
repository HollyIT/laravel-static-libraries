<?php

use HollyIT\StaticLibraries\StaticAssets\Css;
use HollyIT\StaticLibraries\Test\Support\MakesLibraries;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;

beforeEach(function () {
    Config::set('static-libraries.driver', 'dev');
});

it('serves files via dev driver', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('test.css'),
    ]));

    /** @var BinaryFileResponse $response */
    $response = $this->get(route('static_assets_server', [
        'static_file_library' => 'test',
        'static_file' => 'test.css',
    ]))->assertOk();
    $this->assertInstanceOf(File::class, $response->getFile());
});
