# Laravel model validation rules

This package is an alternative to the Laravel built-in validation rules `exist` and `unique`.
This has the following advantages:

 - It uses existing models instead of directly querying the database.
 - The rule can be easily extended with the Eloquent builder.
 - Softdeletes are working

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

## Rules

### ExistEloquent

### UniqueEloquent

## Usage examples

**PostStoreRequest**

```php
public function rules()
{
    $postId = $this->post->id;
    
    return [
        'username' => [new UniqueEloquent(User::class, 'username')],
        'title' => ['string'],
        'content' => ['string'],
        'comments.*.id' => [
            'nullable',
            new ExistEloquent(Comment::class, null, function (Builder $builder) use ($postId) {
                return $builder->where('post_id', $postId);
            }),
        ],
        'comments.*.content' => ['string']
    ];
}
```

**PostUpdateRequest**

```php
public function rules()
{
    $postId = $this->post->id;
    
    return [
        'id' => [new ExistEloquent(Post::class)],
        'username' => [new UniqueEloquent(User::class, 'username')->ignore($postId)],
        'title' => ['string'],
        'content' => ['string'],
        'comments.*.id' => [
            'nullable',
            new ExistEloquent(Comment::class, null, function (Builder $builder) use ($postId) {
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

The MIT License (MIT). Please see [license file](license.md) for more information.
