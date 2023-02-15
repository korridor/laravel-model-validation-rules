<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExistsEloquent implements Rule
{
    /**
     * Class name of model.
     *
     * @var string
     */
    private $model;

    /**
     * Relevant key in the model.
     *
     * @var string|null
     */
    private $key;

    /**
     * Closure that can extend the eloquent builder.
     *
     * @var Closure|null
     */
    private $builderClosure;

    /**
     * Current attribute that is validated.
     *
     * @var string
     */
    private $attribute = null;

    /**
     * Current value that is validated.
     *
     * @var mixed
     */
    private $value = null;

    /**
     * Custom validation message.
     *
     * @var string|null
     */
    private $message = null;

    /**
     * @var bool|null
     */
    private $messageTranslated = null;

    /**
     * Create a new rule instance.
     *
     * @param  string  $model  Class name of model
     * @param  string|null  $key  Relevant key in the model
     * @param  Closure|null  $builderClosure  Closure that can extend the eloquent builder
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->setBuilderClosure($builderClosure);
    }

    /**
     * Set a custom validation message.
     *
     * @param  string  $message
     * @param  bool  $translated
     */
    public function setMessage(string $message, bool $translated): void
    {
        $this->message = $message;
        $this->messageTranslated = $translated;
    }

    /**
     * Set a custom validation message.
     *
     * @param  string  $message
     * @return $this
     */
    public function withMessage(string $message): self
    {
        $this->setMessage($message, false);

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
        $this->setMessage($translationKey, true);

        return $this;
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
        /** @var Model|Builder $builder */
        $builder = new $this->model();
        $modelKeyName = $builder->getKeyName();
        if (null === $this->key) {
            $builder = $builder->where($modelKeyName, $value);
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
        if ($this->message !== null) {
            if ($this->messageTranslated) {
                return trans(
                    $this->message,
                    [
                        'attribute' => $this->attribute,
                        'model' => strtolower(class_basename($this->model)),
                        'value' => $this->value,
                    ]
                );
            } else {
                return $this->message;
            }
        } else {
            return trans(
                'modelValidationRules::validation.exists_model',
                [
                    'attribute' => $this->attribute,
                    'model'     => strtolower(class_basename($this->model)),
                    'value'     => $this->value,
                ]
            );
        }
    }

    /**
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
}
