<?php

namespace Korridor\LaravelModelValidationRules\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UniqueEloquent implements Rule
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
     * @var mixed
     */
    private $ignoreId;

    /**
     * @var string
     */
    private $ignoreColumn;

    /**
     * @var $string
     */
    private $message;

    /**
     * UniqueEloquent constructor.
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
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->value = $value;
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

        return 0 === $builder->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message(): string
    {
        return trans($this->message ?: 'modelValidationRules::validation.unique_model', [
            'attribute' => $this->attribute,
            'model' => class_basename($this->model),
            'value' => $this->value,
        ]);
    }

    /**
     * Set a custom validation message.
     *
     * @return UniqueEloquent
     */
    public function withMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param Closure|null $builderClosure
     */
    public function setBuilderClosure(?Closure $builderClosure): void
    {
        $this->builderClosure = $builderClosure;
    }

    /**
     * @param Closure $builderClosure
     * @return UniqueEloquent
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
    public function setIgnore($id, ?string $column = null): void
    {
        $this->ignoreId = $id;
        $this->ignoreColumn = $column;
    }

    /**
     * @param $id
     * @param string|null $column
     * @return UniqueEloquent
     */
    public function ignore($id, ?string $column = null): self
    {
        $this->setIgnore($id, $column);

        return $this;
    }
}
