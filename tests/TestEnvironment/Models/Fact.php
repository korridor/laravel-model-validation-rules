<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fact extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return BelongsTo|User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
