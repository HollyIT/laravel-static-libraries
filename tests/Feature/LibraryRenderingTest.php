<?php

use HollyIT\StaticLibraries\StaticAssets\Css;
use HollyIT\StaticLibraries\StaticAssets\InlineScript;
use HollyIT\StaticLibraries\StaticAssets\InlineStyle;
use HollyIT\StaticLibraries\StaticAssets\Js;
use HollyIT\StaticLibraries\StaticAssets\JsModule;
use HollyIT\StaticLibraries\Test\Support\MakesLibraries;

it('generates properly orders css files', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css'),
    ]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Css::make('css/test2.css'),
    ])->requires('test'));

    $this->libraries()->require('test2');

    $sheets = $this->libraries()->getStyleSheets();
    $this->assertIsArray($sheets);
    $this->assertCount(2, $sheets);
    $this->assertStringContainsString('test.css', $sheets[0]);
    $this->assertStringContainsString('test2.css', $sheets[1]);
});

it('generates properly orders js files', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js'),
    ]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Js::make('js/test2.js'),
    ])->requires('test'));

    $this->libraries()->require('test2');

    $sheets = $this->libraries()->getScripts();
    $this->assertIsArray($sheets);
    $this->assertCount(2, $sheets);
    $this->assertStringContainsString('test.js', $sheets[0]);
    $this->assertStringContainsString('test2.js', $sheets[1]);

    $rendered = (string) $this->libraries()->renderFooter();
    $this->assertStringContainsString('test.js', $rendered);
});

it('generates properly ordered js files with module maps', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        JsModule::make('js/test.js', 'test-module'),
    ]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        JsModule::make('js/test2.js', 'test-module-2'),
    ])->requires('test'));

    $this->libraries()->require('test2');

    $sheets = $this->libraries()->getScripts();
    $this->assertIsArray($sheets);
    $this->assertCount(2, $sheets);
    $this->assertStringContainsString('test.js', $sheets[0]);
    $this->assertStringContainsString('test2.js', $sheets[1]);
    $this->assertIsArray($this->libraries()->getModuleMap());
    $this->assertCount(2, $this->libraries()->getModuleMap());
    $this->assertEquals('test-module', array_keys($this->libraries()->getModuleMap())[0]);
    $this->assertEquals('test-module-2', array_keys($this->libraries()->getModuleMap())[1]);
    $this->assertStringContainsString('test.js', $this->libraries()->getModuleMap()['test-module']);
    $this->assertStringContainsString('test2.js', $this->libraries()->getModuleMap()['test-module-2']);
});

it('alters js module names', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        JsModule::make('js/test.js', 'test-module'),
    ]));
    $this->libraries()->get('test')->assets->first()->setName('new-name');
    $this->libraries()->require('test');
    $this->assertStringContainsString('new-name', $this->libraries()->renderHead());
});

it('allows scripts to be placed in head', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js')->inHead(),
    ]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Js::make('js/test2.js'),
    ])->requires('test'));

    $this->libraries()->require('test2');

    $scripts = $this->libraries()->getScripts();
    $this->assertIsArray($scripts);
    $this->assertCount(1, $scripts);

    $this->assertStringContainsString('test2.js', $scripts[0]);

    $scripts = $this->libraries()->getScripts(true);
    $this->assertIsArray($scripts);
    $this->assertCount(1, $scripts);

    $this->assertStringContainsString('test.js', $scripts[0]);

    $rendered = (string) $this->libraries()->renderHead();
    $this->assertStringContainsString('test.js', $rendered);
});

it('returns javascript data', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js'),
    ])->withData(['lib' => 'key1', 'lib2' => ['test']]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Js::make('js/test2.js'),
    ])->requires('test')
        ->withData(['lib' => 'key2'])
        ->addData('lib2.1', 'test2')
    );

    $this->libraries()->require('test2');
    $expected = [
        'lib' => 'key2',
        'lib2' => [
            0 => 'test',
            1 => 'test2',
        ],
    ];
    $this->assertEquals($expected, $this->libraries()->getData());
});

it('returns library based data', function () {
    $this->libraries()->withData('test', 'value');
    $this->assertEquals(['test' => 'value'], $this->libraries()->getData());
});

it('overrides library data from the manager', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js'),
    ])->withData(['lib' => 'lib1']));
    $this->libraries()->require('test');
    $this->libraries()->withData('test', 'value');
    $this->libraries()->withData('lib', 'newlib');
    $this->assertEquals(['lib' => 'newlib', 'test' => 'value'], $this->libraries()->getData());
});

it('processes closure based data setters', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js'),
    ])->withData(['lib' => fn () => 'key1', 'lib2' => ['test']]));

    $this->libraries()->require('test');
    $expected = [
        'lib' => 'key1',
        'lib2' => [
            0 => 'test',
        ],
    ];
    $this->assertEquals($expected, $this->libraries()->getData());
});

it('allows data to be removed', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js'),
    ])->withData(['lib' => 'lib1']));
    $this->libraries()->require('test');
    $this->libraries()->withData('test', 'value');
    $this->libraries()->get('test')->removeDataItem('lib');
    $this->assertEquals(['test' => 'value'], $this->libraries()->getData());
});

it('processes closure based data setters recursively', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Js::make('js/test.js'),
    ])->withData(['lib' => fn () => 'key1', 'lib2' => ['test']]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Js::make('js/test2.js'),
    ])->requires('test')
        ->withData(['lib' => 'key2'])
        ->addData('lib2.1', fn () => 'test2')
    );

    $this->libraries()->require('test2');
    $expected = [
        'lib' => 'key2',
        'lib2' => [
            0 => 'test',
            1 => 'test2',
        ],
    ];
    $this->assertEquals($expected, $this->libraries()->getData());
});

it('properly renders tag attributes', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('css/test.css')
        ->withAttribute('media', 'print'),

        Js::make('js/test.js')
            ->withAttribute('async')
            ->withAttribute('type', 'javascript'),
    ]));

    $this->libraries()->require('test');

    $sheets = $this->libraries()->getStyleSheets();
    $this->assertIsArray($sheets);
    $this->assertCount(1, $sheets);
    $this->assertStringContainsString('media="print"', $sheets[0]);

    $scripts = $this->libraries()->getScripts();
    $this->assertIsArray($scripts);
    $this->assertCount(1, $scripts);
    $this->assertStringContainsString('async', $scripts[0]);
    $this->assertStringContainsString('type="javascript"', $scripts[0]);
});

it('renders altered attributes', function () {
    $this->libraries()->add(MakesLibraries::basic('test'));
    $asset = $this->libraries()->get('test')->assets->first();

    $attributes = $asset->getAttributes();
    $attributes[] = 'inline';
    $asset->setAttributes($attributes);
    $this->assertStringContainsString('inline>', (string) $asset);
});

it('does not alter external assets', function () {
    $this->libraries()->add(MakesLibraries::withAssets('test', [
        Css::make('https://example.com/test.css'),
    ]));

    $this->libraries()->add(MakesLibraries::withAssets('test2', [
        Css::make('css/test2.css'),
    ])->requires('test'));

    $this->libraries()->require('test2');

    $sheets = $this->libraries()->getStyleSheets();
    $this->assertIsArray($sheets);
    $this->assertCount(2, $sheets);
    $this->assertStringContainsString('https://example.com/test.css', $sheets[0]);
    $this->assertStringContainsString('test2.css', $sheets[1]);
});

it('renders inline styles', function () {
    $this->libraries()->add(MakesLibraries::basic('test')->assets(
        InlineStyle::make('p {margin:10px 0;}')
    ));
    $this->libraries()->require('test');
    $this->assertStringContainsString('<style>p {margin:10px 0;}</style>', $this->libraries()->renderHead());
});

it('renders closure based inline styles', function () {
    $this->libraries()->add(MakesLibraries::basic('test')->assets(
        InlineStyle::make('p {margin:10px 0;}')
    ));

    $this->libraries()->get('test')->assets->last()->setContents(fn () => 'div{margin:0;}');

    $this->libraries()->require('test');
    $this->assertStringContainsString('<style>div{margin:0;}</style>', $this->libraries()->renderHead());
});

it('renders inline scripts', function () {
    $this->libraries()->add(MakesLibraries::basic('test')->assets(
        InlineScript::make('console.log("OK");')
    ));
    $this->libraries()->require('test');
    $this->assertStringContainsString('<script>console.log("OK");</script>', $this->libraries()->renderFooter());
});

it('renders closure based inline scripts', function () {
    $this->libraries()->add(MakesLibraries::basic('test')->assets(
        InlineScript::make('console.log("OK");')
    ));

    $this->libraries()->get('test')->assets->last()->setContents(fn () => 'alert("OK");');
    $this->libraries()->require('test');
    $this->assertStringContainsString('<script>alert("OK");</script>', $this->libraries()->renderFooter());
});

it('can override static files', function () {
    $this->libraries()->add(MakesLibraries::basic('test'));
    $this->libraries()->get('test')->assets->first()->setFile('newfile.css');
    $this->libraries()->require('test');
    $this->assertStringContainsString('newfile.css', $this->libraries()->renderHead());
});
