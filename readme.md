# Laravel model validation rules

[![Latest Version on Packagist](https://img.shields.io/packagist/v/korridor/laravel-model-validation-rules?style=flat-square)](https://packagist.org/packages/korridor/laravel-model-validation-rules)
[![License](https://img.shields.io/packagist/l/korridor/laravel-model-validation-rules?style=flat-square)](license.md)
[![TravisCI](https://img.shields.io/travis/korridor/laravel-model-validation-rules?style=flat-square)](https://travis-ci.org/korridor/laravel-model-validation-rules)
[![Codecov](https://img.shields.io/codecov/c/github/korridor/laravel-model-validation-rules?style=flat-square)](https://codecov.io/gh/korridor/laravel-model-validation-rules)
[![StyleCI](https://styleci.io/repos/208495858/shield)](https://styleci.io/repos/208495858)

This package is an alternative to the Laravel built-in validation rules `exists` and `unique`.
It uses Eloquent models instead of directly querying the database.

**Advantages**
 - The rule can be easily extended with the Eloquent builder. (scopes etc.)
 - Softdeletes are working out of the box.
 - Logic implemented into the models work in the validation as well. (multi tenancy system, etc.)

## Installation

You can install the package via composer with following command:

```bash
composer require korridor/laravel-model-validation-rules
```

### Requirements

This package is tested for the following Laravel versions:

 - 8.* (PHP 7.3, 7.4, 8.0)
 - 7.* (PHP 7.2, 7.3, 7.4, 8.0)
 - 6.* (PHP 7.2, 7.4, 8.0)
 - 5.8.* (PHP 7.1, 7.4)

## Usage examples

**PostStoreRequest**

```php
use Korridor\LaravelModelValidationRules\Rules\UniqueEloquent;
use Korridor\LaravelModelValidationRules\Rules\ExistsEloquent;
// ...
public function rules(): array
{
    $postId = $this->post->id;
    
    return [
        'username' => [new UniqueEloquent(User::class, 'username')],
        'title' => ['string'],
        'content' => ['string'],
        'comments.*.id' => [
            'nullable',
            new ExistsEloquent(Comment::class, null, function (Builder $builder) use ($postId) {
                return $builder->where('post_id', $postId);
            }),
        ],
        'comments.*.content' => ['string']
    ];
}
```

**PostUpdateRequest**

```php
use Korridor\LaravelModelValidationRules\Rules\UniqueEloquent;
use Korridor\LaravelModelValidationRules\Rules\ExistsEloquent;
// ...
public function rules(): array
{
    $postId = $this->post->id;
    
    return [
        'id' => [new ExistsEloquent(Post::class)],
        'username' => [(new UniqueEloquent(User::class, 'username'))->ignore($postId)],
        'title' => ['string'],
        'content' => ['string'],
        'comments.*.id' => [
            'nullable',
            new ExistsEloquent(Comment::class, null, function (Builder $builder) use ($postId) {
                return $builder->where('post_id', $postId);
            }),
        ],
        'comments.*.content' => ['string']
    ];
}
```

### Custom validation message

If you want to change the validation message for one specific case, you can use the `withMessage(...)` function to add a custom validation message.
With `withCustomTranslation(...)` you can set a custom translation key for the validation message.
As described in detail in the next example ([Customize default validation message](#customize-default-validation-message)), it is possible to use `:attribute`, `:model` and `:value` in the translation.

```php
use Korridor\LaravelModelValidationRules\Rules\UniqueEloquent;
use Korridor\LaravelModelValidationRules\Rules\ExistsEloquent;
// ...
public function rules(): array
{
    $postId = $this->post->id;
    
    return [
        'id' => [(new ExistsEloquent(Post::class))->withMessage('The ID already exists.')],
        'username' => [
            (new UniqueEloquent(User::class, 'username'))
                ->ignore($postId)
                ->withCustomTranslation('validation.custom.username.unique_eloquent')
        ],
        'title' => ['string'],
        'content' => ['string'],
        'comments.*.id' => [
            'nullable',
            new ExistsEloquent(Comment::class, null, function (Builder $builder) use ($postId) {
                return $builder->where('post_id', $postId);
            }),
        ],
        'comments.*.content' => ['string']
    ];
}
```

### Customize default validation message

If you want to customize the translations of the default validation errors you can publish the translations
of the package to the `resources/lang/vendor/modelValidationRules` folder.

```bash
php artisan vendor:publish --provider="Korridor\LaravelModelValidationRules\ModelValidationServiceProvider"
```

You can use the following attributes in the validation message:

 - `attribute`
 - `model`
 - `value`

```php
return [
    'exists_model' => 'A :model with the :attribute ":value" does not exist.',
    'unique_model' => 'A :model with the :attribute ":value" already exists.',
];
```

Example outputs would be:

 - `A user with the id "2" does not exist.`
 - `A user with the id "2" already exists.`

## Contributing

I am open for suggestions and contributions. Just create an issue or a pull request.

### Local docker environment

The `docker` folder contains a local docker environment for development.
The docker workspace has composer and xdebug installed.

```bash
docker-compose run workspace bash
```

### Testing

The `composer test` command runs all tests with [phpunit](https://phpunit.de/).
The `composer test-coverage` command runs all tests with phpunit and creates a coverage report into the `coverage` folder.

### Codeformatting/Linting

The `composer fix` command formats the code with [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).
The `composer lint` command checks the code with [phpcs](https://github.com/squizlabs/PHP_CodeSniffer).

## Credits

The structure of the repository and the TestClass is inspired by the 
project [laravel-validation-rules](https://github.com/spatie/laravel-validation-rules) by [spatie](https://github.com/spatie).

## License

This package is licensed under the MIT License (MIT). Please see [license file](license.md) for more information.
