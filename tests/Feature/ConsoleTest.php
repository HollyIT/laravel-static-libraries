<?php

use HollyIT\StaticLibraries\StaticAssets\Css;
use HollyIT\StaticLibraries\StaticAssets\Js;
use HollyIT\StaticLibraries\Test\Support\MakesLibraries;

it('works', function () {
    $this->withPublicPath();
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css'),
        Js::make('js/test.js'),
    ]));
    $this->artisan('static-libraries:publish')->assertSuccessful();

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Css::make('css/test2.css'),
        Js::make('js/test2.js'),
    ]));

    $this->artisan('static-libraries:list')->assertSuccessful();
});
