<?php

declare(strict_types=1);

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
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/modelValidationRules'),
        ], 'model-validation-rules-translations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang/', 'modelValidationRules');
    }
}
