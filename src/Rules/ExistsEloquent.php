<?php

namespace Korridor\LaravelModelValidationRules\Rules;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Rule;

class ExistsEloquent implements Rule
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
        $this->setBuilderClosure($builderClosure);
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
        /** @var Model $builder */
        $builder = new $this->model();
        if (null === $this->key) {
            $builder = $builder->where((new $this->model())->getKeyName(), $value);
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
        return trans('modelValidationRules::validation.exists_model', [
            'attribute' => $this->attribute,
            'model' => class_basename($this->model),
            'value' => $this->value,
        ]);
    }

    /**
     * @param Closure|null $builderClosure
     */
    public function setBuilderClosure(?Closure $builderClosure)
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
}
