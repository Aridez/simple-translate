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

        Blade::directive('bundle2', function ($component) {
            $encoding = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
            $component = trim($component, "'\" ");

            $string = sprintf(
                '":langs=\'" . json_encode(json_decode(file_get_contents(%s), true), %d) . "\'"',
                'resource_path("simple-translate/bundles/" . App::getLocale() . "/' . $component . '.json")',
                $encoding
            );

            return ('<?php echo ' . $string . '?>');
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
