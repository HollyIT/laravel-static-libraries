<?php

namespace HollyIT\StaticLibraries;

use Carbon\Laravel\ServiceProvider;
use HollyIT\StaticLibraries\Commands\ListStaticAssetsCommand;
use HollyIT\StaticLibraries\Commands\PublishStaticAssetsCommand;
use HollyIT\StaticLibraries\Commands\UnpublishStaticAssetsCommand;
use HollyIT\StaticLibraries\Contracts\StaticAssetsDriver;
use HollyIT\StaticLibraries\Http\Controllers\StaticFallbackController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use RuntimeException;

class StaticLibrariesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->scoped(LibrariesManager::class);
        $this->app->singleton(StaticAssetsDriver::class, function () {
            $driver = config('static-libraries.driver');
            $driverConfig = config('static-libraries.drivers.'.$driver);
            if (! $driverConfig) {
                throw new RuntimeException('Unknown static assets driver '.$driver);
            }

            return app()->make($driverConfig['driver'], ['options' => $driverConfig['options'] ?? []]);
        });
        $this->mergeConfigFrom(__DIR__.'/../config/static-libraries.php', 'static-libraries');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/static-libraries.php.php' => config_path('static-libraries.php'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/static-libraries'),
        ]);

        Blade::directive('static_assets_head', [Directives::class, 'renderHead']);
        Blade::directive('static_assets_footer', [Directives::class, 'renderFooter']);
        $this->commands([PublishStaticAssetsCommand::class, UnpublishStaticAssetsCommand::class, ListStaticAssetsCommand::class]);
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'static-libraries');
        Route::get(Config::get('publish_path', 'static').'/{static_file_library}/{static_file}', StaticFallbackController::class)
            ->where('static_file_library', '[a-zA-Z0-9-_]+')
            ->where('static_file', '.*')
            ->name('static_assets_server');
    }
}
