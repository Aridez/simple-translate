<?php

namespace Aridez\SimpleTranslate;

use Aridez\SimpleTranslate\Console\Bundle;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SimpleTranslateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                Bundle::class,
            ]);
        }

        //register the blade directive calling a helper function on src/helpers.php
        Blade::directive('bundle', function ($bundle) {
            return ":__=\"function(translation) { return this.langs[translation] }\" :langs=\"{{json_bundle_translations($bundle)}}\"";
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
