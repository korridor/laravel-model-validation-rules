<?php

namespace Korridor\LaravelModelValidationRules\Tests\Feature;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Korridor\LaravelModelValidationRules\Rules\ExistEloquent;
use Korridor\LaravelModelValidationRules\Tests\TestCase;
use Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models\Fact;
use Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models\User;
use Lang;
use Str;

class ExistEloquentTest extends TestCase
{
    use RefreshDatabase;

    /*
     * Tests with primary key
     */

    public function testThatValidationFailsIfEntryDoesNotExistInDatabase()
    {
        $rule = new ExistEloquent(User::class);
        $this->assertFalse($rule->passes('id', 1));
    }

    public function testThatValidationFailsIfEntryIsSoftdeleted()
    {
        $rule = new ExistEloquent(User::class);
        $user = User::create([
            'id' => 1,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $this->assertFalse($rule->passes('id', 1));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testThatValidationFailsIfEntryWithCorrectAttributeExists()
    {
        $rule = new ExistEloquent(User::class);
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $this->assertTrue($rule->passes('id', 2));
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with other attribute
     */

    public function testThatValidationFailsIfEntryDoesNotExistInDatabaseUsingOtherAttribute()
    {
        $rule = new ExistEloquent(User::class, 'other_id');
        $this->assertFalse($rule->passes('id', 1));
    }

    public function testThatValidationFailsIfEntryIsSoftdeletedUsingOtherAttribute()
    {
        $rule = new ExistEloquent(User::class, 'other_id');
        $user = User::create([
            'id' => 1,
            'other_id' => 3,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $this->assertFalse($rule->passes('id', 3));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testThatValidationFailsIfEntryWithCorrectAttributeExistsUsingOtherAttribute()
    {
        $rule = new ExistEloquent(User::class, 'other_id');
        User::create([
            'id' => 2,
            'other_id' => 4,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $this->assertTrue($rule->passes('id', 4));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with builder closure
     */

    public function testThatValidationPassesIfRuleChecksThatFactExistsAndBelongsToUser()
    {
        $rule = new ExistEloquent(Fact::class, null, function (Builder $builder) {
            return $builder->where('user_id', 6);
        });
        User::create([
            'id' => 6,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        Fact::create([
            'id' => 1,
            'user_id' => 6,
            'type' => 'type1',
            'description' => 'Long desc',
        ]);
        $this->assertTrue($rule->passes('id', 1));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    /*
     * Test language support
     */

    public function testThatValidationParametersAreWorkingCorrectly()
    {
        Lang::addLines([
            'validation.exist_model' => ':attribute :model :value',
        ], Lang::getLocale());
        $rule = new ExistEloquent(User::class);
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule->passes('id', 2);
        $this->assertEquals('id User 2', $rule->message());
    }
}
