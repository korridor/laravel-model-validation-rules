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

### Translation

If you want to customize the translations of the validation errors you can publish the translations 
of the package to the `resources/lang/vendor/modelValidationRules` folder.

```bash
php artisan vendor:publish --provider="Korridor\LaravelModelValidationRules\ModelValidationServiceProvider"
```

### Requirements

This package is tested for the following Laravel versions:

 - 8.*
 - 7.*
 - 6.*
 - 5.8.*
 - 5.7.* (stable only)
 - 5.6.* (stable only)

## Usage examples

**PostStoreRequest**

```php
use Korridor\LaravelModelValidationRules\Rules\UniqueEloquent;
use Korridor\LaravelModelValidationRules\Rules\ExistsEloquent;
// ...
public function rules()
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
public function rules()
{
    $postId = $this->post->id;
    
    return [
        'id' => [new ExistsEloquent(Post::class)],
        'username' => [new UniqueEloquent(User::class, 'username')->ignore($postId)],
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

## Contributing

I am open for suggestions and contributions. Just create an issue or a pull request.

### Testing

```bash
composer test
composer test-coverage
```

### Codeformatting/Linting

```bash
composer fix
composer lint
```

## Credits

The structure of the repository and the TestClass is inspired by the 
project [laravel-validation-rules](https://github.com/spatie/laravel-validation-rules) by [spatie](https://github.com/spatie).

## License

This package is licensed under the MIT License (MIT). Please see [license file](license.md) for more information.
