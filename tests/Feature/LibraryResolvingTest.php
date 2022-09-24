<?php

use HollyIT\StaticLibraries\Positioning\PositionAfter;
use HollyIT\StaticLibraries\Positioning\PositionBefore;
use HollyIT\StaticLibraries\Test\Support\MakesLibraries;

it('requires dependencies in the proper order', function () {
    $this->libraries()->add(MakesLibraries::basic('test2')->requires('test'));
    $this->libraries()->add(MakesLibraries::basic('test'));
    $this->libraries()->require('test2');

    $this->assertLibraryOrder(['test', 'test2']);
});

it('evaluates dependencies as callback', function () {
    $this->libraries()->add(MakesLibraries::basic('test2')->requires(fn () => ['test']));
    $this->libraries()->add(MakesLibraries::basic('test'));
    $this->libraries()->require('test2');
    $this->assertLibraryOrder(['test', 'test2']);
});

it('adds a required with library', function () {
    $this->libraries()->add(MakesLibraries::basic('test2'));
    $this->libraries()->add(MakesLibraries::basic('test')->requiredWith('test2'));
    $this->libraries()->require('test2');
    $this->assertLibraryOrder(['test2', 'test']);
});

it('properly orders require before', function () {
    $this->libraries()->add(MakesLibraries::basic('test2'));
    $this->libraries()->add(MakesLibraries::basic('test3'));
    $this->libraries()->add(MakesLibraries::basic('test')->position(new PositionBefore('test2')));
    $this->libraries()->require('test2', 'test3', 'test');
    $this->assertLibraryOrder(['test', 'test2', 'test3']);
});

it('ensures required before library is before all defined libraries', function () {
    $this->libraries()->add(MakesLibraries::basic('test2'));
    $this->libraries()->add(MakesLibraries::basic('test3'));
    $this->libraries()->add(MakesLibraries::basic('test')->position(new PositionBefore(['test2', 'test3'])));
    $this->libraries()->require('test2', 'test3', 'test');
    $this->assertLibraryOrder(['test', 'test2', 'test3']);
});

it('properly orders require after', function () {
    $this->libraries()->add(MakesLibraries::basic('test2'));
    $this->libraries()->add(MakesLibraries::basic('test3'));
    $this->libraries()->add(MakesLibraries::basic('test')->position(new PositionAfter('test2')));
    $this->libraries()->require('test2', 'test', 'test3');
    $this->assertLibraryOrder(['test2', 'test', 'test3']);
});

it('ensures required after library is before all defined libraries', function () {
    $this->libraries()->add(MakesLibraries::basic('test2'));
    $this->libraries()->add(MakesLibraries::basic('test3'));
    $this->libraries()->add(MakesLibraries::basic('test')->position(new PositionAfter(['test2', 'test3'])));
    $this->libraries()->require('test', 'test2', 'test3');
    $this->assertLibraryOrder(['test2', 'test3', 'test']);
});
