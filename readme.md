# Laravel model validation rules

## Installation

`composer require korridor/laravel-model-validation-rules`

## Usage

```php
public function rules()
{
    $postId = $this->post->id;
    
    return [
        'id' => [new ExistEloquent(Post::class)],
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

### Testing

`composer test`
`composer test-coverage`

### Codeformatting

`composer fix`
`composer lint`

## License

The MIT License (MIT). Please see [license file](license.md) for more information.
