<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Rules\Concerns;

use BackedEnum;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Create a new query for the model, with the builder closure applied.
     *
     * @param  class-string<Model>  $model
     * @return Builder<Model>
     */
    private function newModelQuery(string $model, ?Closure $builderClosure): Builder
    {
        /** @var Builder<Model> $builder */
        $builder = (new $model())->newQuery();

        if ($builderClosure === null) {
            return $builder;
        }

        $result = $builderClosure($builder);
        if ($result instanceof Builder) {
            /** @var Builder<Model> $result */
            $builder = $result;
        }

        $this->groupWhereClauses($builder);

        return $builder;
    }

    /**
     * Wrap the where clauses added by the builder closure in a nested group,
     * so an "or where" in the closure can not bypass the constraints added by the rule.
     *
     * @param  Builder<Model>  $builder
     */
    private function groupWhereClauses(Builder $builder): void
    {
        $query = $builder->getQuery();

        $hasOrWhere = false;
        foreach ($query->wheres as $where) {
            // Covers "or" and "or not".
            if (str_starts_with(strtolower((string) ($where['boolean'] ?? 'and')), 'or')) {
                $hasOrWhere = true;
                break;
            }
        }
        if (!$hasOrWhere) {
            return;
        }

        $nested = $query->forNestedWhere();
        $nested->wheres = $query->wheres;
        $nested->setBindings($query->getRawBindings()['where'], 'where');

        $query->wheres = [];
        $query->setBindings([], 'where');
        $query->addNestedWhereQuery($nested);
    }
}
