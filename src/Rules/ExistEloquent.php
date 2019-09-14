<?php

namespace Korridor\LaravelModelValidationRules\Rules;

use Closure;
use Eloquent;
use Illuminate\Contracts\Validation\Rule;

class ExistEloquent implements Rule
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var string|null
     */
    private $key;

    /**
     * @var Closure|null
     */
    private $builderClosure;

    /**
     * @var string
     */
    private $attribute;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Create a new rule instance.
     *
     * @param string $model
     * @param string|null $key
     * @param Closure|null $builderClosure
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->builderClosure = $builderClosure;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->value = $value;
        /** @var Eloquent $builder */
        $builder = new $this->model();
        if (null === $this->key) {
            $builder = $builder->where($builder->getKeyName(), $value);
        } else {
            $builder = $builder->where($this->key, $value);
        }
        if (null !== $this->builderClosure) {
            $builderClosure = $this->builderClosure;
            $builder = $builderClosure($builder);
        }

        return $builder->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.exist_model', [
            'attribute' => $this->attribute,
            'model' => class_basename($this->model),
            'value' => $this->value,
        ]);
    }
}
