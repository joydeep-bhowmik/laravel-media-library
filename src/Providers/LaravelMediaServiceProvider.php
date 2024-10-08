<?php

namespace JoydeepBhowmik\LaravelMediaLibary\Providers;


use Illuminate\Support\ServiceProvider;

class LaravelMediaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the migration files
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'media-library-migrations');

        // Publish the config file
        $this->publishes([
            __DIR__ . '/../config/media.php' => config_path('media.php'),
        ], 'media-library-config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration file
        $this->mergeConfigFrom(
            __DIR__ . '/../config/media.php',
            'media'
        );
    }
}
