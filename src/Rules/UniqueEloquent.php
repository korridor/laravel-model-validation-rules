<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
     * UniqueEloquent constructor.
     *
     * @param  class-string<Model> $model Class name of model.
     * @param  string|null  $key Relevant key in the model.
     * @param  Closure|null  $builderClosure Closure that can extend the eloquent builder
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->setBuilderClosure($builderClosure);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
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

        if ($builder->exists()) {
            if ($this->customMessage !== null) {
                $fail($this->customMessage);
            } else {
                $fail($this->customMessageTranslationKey ?? 'modelValidationRules::validation.unique_model')->translate([
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
     * @param  string  $message
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
     * @param  string  $translationKey
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
     * @param  Closure|null  $builderClosure
     */
    public function setBuilderClosure(?Closure $builderClosure): void
    {
        $this->builderClosure = $builderClosure;
    }

    /**
     * @param  Closure  $builderClosure
     * @return $this
     */
    public function query(Closure $builderClosure): self
    {
        $this->setBuilderClosure($builderClosure);

        return $this;
    }

    /**
     * @param  mixed  $id
     * @param  string|null  $column
     */
    public function setIgnore(mixed $id, ?string $column = null): void
    {
        $this->ignoreId = $id;
        $this->ignoreColumn = $column;
    }

    /**
     * @param mixed $id
     * @param  string|null  $column
     * @return UniqueEloquent
     */
    public function ignore(mixed $id, ?string $column = null): self
    {
        $this->setIgnore($id, $column);

        return $this;
    }
}
