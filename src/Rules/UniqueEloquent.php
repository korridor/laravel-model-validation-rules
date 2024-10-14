<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueEloquent implements ValidationRule
{
    /**
     * Class name of model.
     *
     * @var class-string<Model>
     */
    private string $model;

    /**
     * Relevant key in the model.
     *
     * @var string|null
     */
    private ?string $key;

    /**
     * Closure that can extend the eloquent builder
     *
     * @var Closure|null
     */
    private ?Closure $builderClosure;

    /**
     * @var mixed
     */
    private mixed $ignoreId = null;

    /**
     * @var string|null
     */
    private ?string $ignoreColumn = null;

    /**
     * Custom validation message.
     *
     * @var string|null
     */
    private ?string $customMessage = null;

    /**
     * Translation key for custom validation message.
     *
     * @var string|null
     */
    private ?string $customMessageTranslationKey = null;

    /**
     * Include soft deleted models in the query.
     *
     * @var bool
     */
    private bool $includeSoftDeleted = false;

    /**
     * @var bool Whether the ID is a UUID
     */
    private bool $isFieldUuid = false;

    /**
     * UniqueEloquent constructor.
     *
     * @param class-string<Model> $model Class name of model.
     * @param string|null $key Relevant key in the model.
     * @param Closure|null $builderClosure Closure that can extend the eloquent builder
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->setBuilderClosure($builderClosure);
    }

    /**
     * @param class-string<Model> $model Class name of model.
     * @param string|null $key Relevant key in the model.
     * @param Closure|null $builderClosure Closure that can extend the eloquent builder
     */
    public static function make(string $model, ?string $key = null, ?Closure $builderClosure = null): self
    {
        return new self($model, $key, $builderClosure);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->isFieldUuid) {
            if (!is_string($value) || !Str::isUuid($value)) {
                return;
            }
        }
        /** @var Model|Builder $builder */
        $builder = new $this->model();
        $modelKeyName = $builder->getKeyName();
        $builder = $builder->where(null === $this->key ? $modelKeyName : $this->key, $value);
        if (null !== $this->builderClosure) {
            $builderClosure = $this->builderClosure;
            $builder = $builderClosure($builder);
        }
        if (null !== $this->ignoreId) {
            $builder = $builder->where(
                null === $this->ignoreColumn ? $modelKeyName : $this->ignoreColumn,
                '!=',
                $this->ignoreId
            );
        }

        if ($this->includeSoftDeleted) {
            $builder = $builder->withTrashed();
        }

        if ($builder->exists()) {
            if ($this->customMessage !== null) {
                $fail($this->customMessage);
            } else {
                $fail($this->customMessageTranslationKey ?? 'modelValidationRules::validation.unique_model')
                    ->translate([
                        'attribute' => $attribute,
                        'model' => strtolower(class_basename($this->model)),
                        'value' => $value,
                    ]);
            }
        }
    }

    /**
     * Set a custom validation message.
     *
     * @param string $message
     * @return $this
     */
    public function withMessage(string $message): self
    {
        $this->customMessage = $message;

        return $this;
    }

    /**
     * Set a translated custom validation message.
     *
     * @param string $translationKey
     * @return $this
     */
    public function withCustomTranslation(string $translationKey): self
    {
        $this->customMessageTranslationKey = $translationKey;

        return $this;
    }

    /**
     * Set a closure that can extend the eloquent builder.
     *
     * @param Closure|null $builderClosure
     */
    public function setBuilderClosure(?Closure $builderClosure): void
    {
        $this->builderClosure = $builderClosure;
    }

    /**
     * @param Closure $builderClosure
     * @return $this
     */
    public function query(Closure $builderClosure): self
    {
        $this->setBuilderClosure($builderClosure);

        return $this;
    }

    /**
     * @param mixed $id
     * @param string|null $column
     */
    public function setIgnore(mixed $id, ?string $column = null): void
    {
        $this->ignoreId = $id;
        $this->ignoreColumn = $column;
    }

    /**
     * @param mixed $id
     * @param string|null $column
     * @return UniqueEloquent
     */
    public function ignore(mixed $id, ?string $column = null): self
    {
        $this->setIgnore($id, $column);

        return $this;
    }

    /**
     * Activate or deactivate including soft deleted models in the query.
     *
     * @param bool $includeSoftDeleted
     * @return void
     */
    public function setIncludeSoftDeleted(bool $includeSoftDeleted): void
    {
        $this->includeSoftDeleted = $includeSoftDeleted;
    }

    /**
     * The field has the data type UUID.
     * If a value is not a UUID, the validation will be skipped.
     * This is useful for example for Postgres databases where queries fail if a field with UUID data type is queried with a non-UUID value.
     *
     * @return $this
     */
    public function uuid(): self
    {
        $this->isFieldUuid = true;

        return $this;
    }

    /**
     * Activate including soft deleted models in the query.
     *
     * @return $this
     */
    public function includeSoftDeleted(): self
    {
        $this->setIncludeSoftDeleted(true);

        return $this;
    }
}
