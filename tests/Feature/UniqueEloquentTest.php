<?php

namespace Korridor\LaravelModelValidationRules\Tests\Feature;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Korridor\LaravelModelValidationRules\Rules\UniqueEloquent;
use Korridor\LaravelModelValidationRules\Tests\TestCase;
use Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models\Fact;
use Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models\User;

class UniqueEloquentTest extends TestCase
{
    use RefreshDatabase;

    /*
     * Tests with primary key
     */

    public function testThatValidationPassesWhenIfEntryDoesNotExistInDatabase()
    {
        $rule = new UniqueEloquent(User::class);
        $this->assertTrue($rule->passes('id', 1));
    }

    public function testThatValidationPassesIfEntryIsSoftdeleted()
    {
        $rule = new UniqueEloquent(User::class);
        $user = User::create([
            'id' => 1,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $this->assertTrue($rule->passes('id', 1));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testThatValidationFailsIfEntryWithCorrectAttributeExists()
    {
        $rule = new UniqueEloquent(User::class);
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $this->assertFalse($rule->passes('id', 2));
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with other attribute
     */

    public function testThatValidationPassesIfEntryDoesNotExistInDatabaseUsingOtherAttribute()
    {
        $rule = new UniqueEloquent(User::class, 'other_id');
        $this->assertTrue($rule->passes('id', 1));
    }

    public function testThatValidationPassesIfEntryIsSoftdeletedUsingOtherAttribute()
    {
        $rule = new UniqueEloquent(User::class, 'other_id');
        $user = User::create([
            'id' => 1,
            'other_id' => 3,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $this->assertTrue($rule->passes('id', 3));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testThatValidationFailsIfEntryWithCorrectAttributeExistsUsingOtherAttribute()
    {
        $rule = new UniqueEloquent(User::class, 'other_id');
        User::create([
            'id' => 2,
            'other_id' => 4,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $this->assertFalse($rule->passes('id', 4));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with builder closure
     */

    public function testThatValidationFailsIfRuleChecksThatFactExistsAndBelongsToUserUsingConstructor()
    {
        $rule = new UniqueEloquent(Fact::class, null, function (Builder $builder) {
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
        $this->assertFalse($rule->passes('id', 1));
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    public function testThatValidationFailsIfRuleChecksThatFactExistsAndBelongsToUserUsingFunction()
    {
        $rule = (new UniqueEloquent(Fact::class))->query(function (Builder $builder) {
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
        $this->assertFalse($rule->passes('id', 1));
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
            'validation.unique_model' => ':attribute :model :value',
        ], Lang::getLocale(), 'modelValidationRules');
        $rule = new UniqueEloquent(User::class);
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

    /*
     * Test ignore
     */

    public function testIgnoringEntryWithDefaultIdColumn()
    {
        $rule = (new UniqueEloquent(User::class))->ignore(1);
        User::create([
            'id' => 1,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name1@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'id' => 2,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name2@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $this->assertTrue($rule->passes('id', 1));
    }

    public function testIgnoringEntryWithGivenIdColum()
    {
        $rule = (new UniqueEloquent(User::class))->ignore('name1@test.com', 'email');
        User::create([
            'id' => 1,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name1@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'id' => 2,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name2@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $this->assertTrue($rule->passes('id', 'name1@test.com'));
    }
}
