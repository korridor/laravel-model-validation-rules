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
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'validationRules');
    }
}
