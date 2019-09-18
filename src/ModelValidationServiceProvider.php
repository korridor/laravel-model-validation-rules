<?php

namespace Korridor\LaravelModelValidationRules;

use Illuminate\Support\ServiceProvider;

/**
 * Class ModelValidationServiceProvider.
 */
class ModelValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/modelValidationRules'),
        ]);
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'modelValidationRules');
    }
}
