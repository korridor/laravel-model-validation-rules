<?php

namespace Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return HasMany|Fact
     */
    public function user(): HasMany
    {
        return $this->hasMany(Fact::class);
    }
}
