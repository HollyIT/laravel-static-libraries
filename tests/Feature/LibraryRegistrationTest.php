<?php

use HollyIT\StaticLibraries\Events\PrepareStaticLibraryOrder;
use HollyIT\StaticLibraries\Events\RegisterStaticLibrariesEvent;
use HollyIT\StaticLibraries\Test\Support\MakesLibraries;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

it('registers libraries', function () {
    $this->libraries()->add(MakesLibraries::basic('test'));
    $this->assertTrue($this->libraries()->has('test'));
});

it('throws an exception when requiring an unknown library', function () {
    $this->libraries()->add(MakesLibraries::basic('test'));
    $this->expectExceptionMessage('Unknown static library test2');
    $this->libraries()->require(['test', 'test2']);
});

it('triggers library registration events', function () {
    Event::fake([
        RegisterStaticLibrariesEvent::class,
        PrepareStaticLibraryOrder::class,
    ]);

    $this->libraries()->add(MakesLibraries::basic('test'));
    Event::assertDispatched(RegisterStaticLibrariesEvent::class);

    $this->libraries()->getOrdered();
    Event::assertDispatched(PrepareStaticLibraryOrder::class);
});

it('throws an exception when required library is not found', function () {
    $this->libraries()->add(MakesLibraries::basic('test2')->requires('test'));
    $this->libraries()->require('test2');
    $this->expectExceptionMessage('Required static library test could not be located');
    $this->libraries()->getOrdered();
});

it('can alter libraries', function () {
    $this->libraries()->add(MakesLibraries::basic('test2'));
    $this->libraries()->add(MakesLibraries::basic('test'));
    Event::listen(PrepareStaticLibraryOrder::class, function (PrepareStaticLibraryOrder $event) {
        $event->manager->get('test2')->requires('test');
    });

    $this->libraries()->require('test2');
    $this->assertLibraryOrder(['test', 'test2']);
});

it('can remove a required library', function () {
    $this->libraries()->add(MakesLibraries::basic('test'));
    Event::listen(PrepareStaticLibraryOrder::class, function (PrepareStaticLibraryOrder $event) {
        $event->manager->removeRequired('test');
    });

    $this->libraries()->require('test');
    $this->assertEmpty($this->libraries()->getOrdered());
});

it('throws an exception on invalid driver', function () {
    Config::set('static-libraries.driver', 'null');
    $this->expectExceptionMessage('Unknown static assets driver null');
    $this->libraries();
});
