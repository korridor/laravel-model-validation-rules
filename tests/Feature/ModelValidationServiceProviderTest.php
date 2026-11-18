<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Tests\Feature;

use Illuminate\Support\ServiceProvider;
use Korridor\LaravelModelValidationRules\ModelValidationServiceProvider;
use Korridor\LaravelModelValidationRules\Tests\TestCase;

class ModelValidationServiceProviderTest extends TestCase
{
    public function testTranslationsArePublishedToLangPath(): void
    {
        // Act
        $paths = ServiceProvider::pathsToPublish(
            ModelValidationServiceProvider::class,
            'model-validation-rules-translations'
        );

        // Assert
        $this->assertSame([lang_path('vendor/modelValidationRules')], array_values($paths));
    }
}
