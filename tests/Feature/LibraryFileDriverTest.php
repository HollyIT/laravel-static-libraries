<?php

use HollyIT\StaticLibraries\Library;
use HollyIT\StaticLibraries\StaticAssets\Css;
use HollyIT\StaticLibraries\StaticAssets\Js;
use HollyIT\StaticLibraries\Test\Support\MakesLibraries;

it('serves files via file driver', function () {
    $this->withPublicPath();
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css'),
        Js::make('js/test.js'),

    ]));

    $response = $this->get('/static/test/css/test.css');
    $response->assertOk();
    $response = $this->get('/static/test/js/test.js');
    $response->assertOk();
});

it('publishes libraries to the public static directory', function () {
    $this->withPublicPath();
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css'),
        Js::make('js/test.js'),
    ]));

    $this->artisan('static-libraries:publish')->assertSuccessful();
    $this->assertFileExists(public_path('static/test/css/test.css'));
    $this->assertFileExists(public_path('static/test/js/test.js'));
});

it('overwrites modified files on publish', function () {
    $this->withPublicPath();
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css'),
        Js::make('js/test.js'),
    ]));
    $this->artisan('static-libraries:publish')->assertSuccessful();
    $this->assertFileExists(public_path('static/test/css/test.css'));
    $mtime = filemtime(public_path('static/test/css/test.css'));
    $this->assertFileExists(public_path('static/test/js/test.js'));
    $library = $this->libraries()->get('test');
    $path = $library->filePath($library->assets->first()->getFile());
    unlink($path);
    sleep(1); // This is dirty but need mtime to update.
    file_put_contents($path, 'test.css');
    $this->artisan('static-libraries:publish')->assertSuccessful();
    $newMtime = filemtime(public_path('static/test/css/test.css'));
    $this->assertNotEquals($mtime, $newMtime);
});

it('unpublishes libraries from the public static directory', function () {
    $this->withPublicPath();
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css'),
        Js::make('js/test.js'),
    ]));
    $this->artisan('static-libraries:publish')->assertSuccessful();
    $this->assertDirectoryExists(public_path('static/test'));
    $this->artisan('static-libraries:unpublish')->assertSuccessful();
    $this->assertDirectoryDoesNotExist(public_path('static/test'));
});

it('will not publish libraries defined in the public static directory', function () {
    $this->libraries()->add(
        Library::make('test', public_path())
            ->assets(Css::make('test.css'))
    );
    file_put_contents(public_path('test.css'), '');
    $this->assertTrue($this->libraries()->get('test')->isInPublic());
    $this->artisan('static-libraries:publish')->assertSuccessful();
    $this->artisan('static-libraries:unpublish')->assertSuccessful();
    $this->assertFileExists(public_path('test.css'));
});

it('generates proper public url', function () {
    $library = Library::make('test', public_path())
        ->assets(Css::make('test.css'));
    $this->libraries()->add($library);
    file_put_contents(public_path('test.css'), '');
    $this->assertStringStartsWith(url('test.css'), $library->assets[0]->resolveFileUrl());
});
