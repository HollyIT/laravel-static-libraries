# Introduction

(please note that this documentation is a work in progress.)

Static Libraries are a way to corral all your various static assets used on your webpages, such as various stylesheets
and javascript libraries. They provide a mechanism to easily include multiple items, without needing to remember
dependency or orders.

Static Libraries are registered via an event. This way if you don't require any libraries, then nothing happens and
performance is saved. So if you want to register a library, simply add this to the boot() method of your service
provider:

```php
Event::listen(\HollyIT\StaticLibraries\Events\RegisterStaticLibrariesEvent::class,
    function (\HollyIT\StaticLibraries\Events\RegisterStaticLibrariesEvent $event) {
    $event->manager->add(
        Library::make('package1', __DIR__ . '/../dist')
            ->assets(
                Css::make('css/package1.css'),
                Js::make('js/package1.js')
            )
            ->requires('package1'),
        Library::make('package2', __DIR__ . '/../dist')
            ->assets(
                Css::make('css/package2.css'),
                Js::make('js/package2.js')
            )
    );
});
```

The signature of the Library::make is $libraryName, $basePath. The above example is assuming you are developing a
package. You can also use libraries in your own app and set the base path to a location in public_path or anywhere else.
This package will know how to handle it.

## Installation

First, you will need to require this package:

```bash
composer require hollyit/laravel-static-libraries
```

After that, you need to update your blade files to inject the assets. So in the head of your blade file, simply add:

`@static_assets_head()`

And before the closing </body> tag, add:

`@static_assets_footer()`

These two directives call a series of other blade templates to assemble the code of all the required libraries. You can
feel free to publish the templates to override them, but the defaults should suffice.

## Understanding libraries

As you may have noticed above, libraries add asserts, which are the actual javascript or css files/code you wish to add.
There are currently 5 types of assets available.

The first three asset types are Css, Js, and JsModule and are file-based assets. You either add them as a relative path
to your library's base_path or as a URL (thus allowing for CDN or external files).

The final two asset types are InlineStyle and InlineScript. As the name applies, these are inline-style assets, that
will inject the provided code between the appropriate `<style>` or `<script>` tags.

On all assets, you can supply attributes to add to the appropriate tags. For example:

```php
Css::make('css/package1.css', ['media' => print])
```

will add in a style sheet with the `media="print"` attribute.

### JsModule

This package provides a custom JsModule asset type. Ths signature for this asset type's factory is a little different:

```php
make(string $file, string $name, array $attributes = [])
```

When rendered it will add in the `type="module"` and `name="{$name}"` attributes. But JsModules offer much more than
that.

A proposed standard for ES Modules is import-map's. You can read about them [here](https://wicg.github.io/import-maps/).
While adaptation isn't that wide yet, this module will inject a shim to provide support to browser's that need it. This
shim can be overridden in the static-libraries config file.

When you add JsModules to your library, this package will automatically render an import map for your page. Here's an
example of what I mean:

```html

<script type="importmap">
            {"imports":
                {
                    "module1":"http:\/\/localhost\/js\/module1.js?_c=efb6379fdf66",
                    "module2":"http:\/\/localhost\/js\/module2.js?_c=338cbc12e51d"
                }
            }
        
</script>
```

This map was automatically generated from the following libraries:

```php
Library::make('test1')
    ->assets(
        \HollyIT\StaticLibraries\StaticAssets\JsModule::make('js/module1.js', 'module1')
    )

Library::make('test2')->assets(
    \HollyIT\StaticLibraries\StaticAssets\JsModule::make('js/module2.js', 'module2')
)
```

## Dependency Management

Libraries can also manage dependencies. By chaining a -`>requires(...libraries)` to your library declaration, any time that library is required, it will automatically require and place before it the libraries defines in requires().

Another dependency feature is adding a library when another one is required. For example, say you are using TinyMCE and have another library that provides a plugin for it. Well if you define a library:

```php
    Library::make('tinymce_plugin', __DIR__)
    ->requiredWith('tinymce')
```
Then simply doing a $libraries->require('tinymce') will also include 'tinymce_plugin'. You can also specify if that library should be added before or after the parent library:

```php
    Library::make('tinymce_plugin', __DIR__)
    ->requiredWith('tinymce')
    ->position(new PositionBefore('tinymce'))
```

Two positioning rules are offered, PositionBefore and PositionAfter.

## Asset serving

This package uses a concept of drivers to handle publishing and serving your static assets. It ships with two drivers, but more can be added. The driver can be configured in the static-libraries config file, or by setting `STATIC_LIBRARIES_DRIVER` in your .env file.

##### Dev Driver

This is the simplest driver. It serves all your local assets via Laravel.

##### File Driver

This driver is what's known as a publishable driver. That means it will copy the various files from their location over to your public_path. If the files aren't found there, then it will fall back to utilizing Laravel to serve the files. This driver also integrates cache busting, based on the hash of the static file.

To publish files, simple issue this artisan command:

```bash
php artisan static-libraries:publish
```

You can also unpublish libraries with:

```bash
php artisan static-libraries:unpublish
```

**An important note here**: As more packages utilize static libraries, it can be easy to end up with stale published assets. To prevent that, it is recommended to add the following to the scripts section of your project's composer.json:

```
        "post-update-cmd": [
            "@php artisan static-libraries:publish"
        ],
```

That way whenever you update your packages, any libraries that have been modified will publish their files.

### Development info

The package offers one other artisan command, static-libraries:info This will list out all the registered libraries, their publishing status and if the published files are out of date or not.

## Future Development

I would very much appreciate anyone wanting S3 support to please open a PR and create a driver to handle that. I'm not a big user of S3, but this package will work fine with it and using the fallback rules of S3, should be able to fall back to serving files through laravel if needed.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.









