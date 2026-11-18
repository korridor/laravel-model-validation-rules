<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Rules\Concerns;

use BackedEnum;

/**
 * Shared query building logic for the Eloquent validation rules.
 *
 * @internal
 */
trait BuildsModelQuery
{
    /**
     * Whether the value can safely be used as a single where binding.
     * Arrays would be silently reduced to their first element by the query builder
     * and other objects (e.g. uploaded files) are not meaningful column values.
     */
    private function isQueryableValue(mixed $value): bool
    {
        return $value === null || is_scalar($value) || $value instanceof BackedEnum;
    }

    /**
     * Value for the :value placeholder of the validation message.
     */
    private function messageValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
