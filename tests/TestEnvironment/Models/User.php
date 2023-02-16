<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * @return HasMany<Fact>
     */
    public function user(): HasMany
    {
        return $this->hasMany(Fact::class);
    }
}
