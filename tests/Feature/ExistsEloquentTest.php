<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Tests\Feature;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Korridor\LaravelModelValidationRules\Rules\ExistsEloquent;
use Korridor\LaravelModelValidationRules\Tests\TestCase;
use Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models\Fact;
use Korridor\LaravelModelValidationRules\Tests\TestEnvironment\Models\User;

class ExistsEloquentTest extends TestCase
{
    use RefreshDatabase;

    /*
     * Tests with primary key
     */

    public function testValidationFailsIfEntryDoesNotExistInDatabase(): void
    {
        // Arrange
        $rule = new ExistsEloquent(User::class);

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertFalse($isValid);
    }

    public function testValidationFailsIfEntryIsSoftDeleted(): void
    {
        // Arrange
        $user = User::create([
            'id' => 1,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $rule = new ExistsEloquent(User::class);

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertFalse($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testValidationPassesIfEntryWithCorrectAttributeExists(): void
    {
        // Arrange
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = new ExistsEloquent(User::class);

        // Act
        $isValid = $rule->passes('id', 2);

        // Assert
        $this->assertTrue($isValid);
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with other attribute
     */

    public function testValidationFailsIfEntryDoesNotExistInDatabaseUsingOtherAttribute(): void
    {
        // Arrange
        $rule = new ExistsEloquent(User::class, 'other_id');

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertFalse($isValid);
    }

    public function testValidationFailsIfEntryIsSoftDeletedUsingOtherAttribute(): void
    {
        // Arrange
        $user = User::create([
            'id' => 1,
            'other_id' => 3,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $rule = new ExistsEloquent(User::class, 'other_id');

        // Act
        $isValid = $rule->passes('id', 3);

        // Assert
        $this->assertFalse($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testValidationPassesIfEntryWithCorrectAttributeExistsUsingOtherAttribute(): void
    {
        // Arrange
        User::create([
            'id' => 2,
            'other_id' => 4,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = new ExistsEloquent(User::class, 'other_id');

        // Act
        $isValid = $rule->passes('id', 4);

        $this->assertTrue($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with builder closure
     */

    public function testValidationPassesIfRuleChecksThatFactExistsAndBelongsToUserUsingConstructor(): void
    {
        // Arrange
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
        $rule = new ExistsEloquent(Fact::class, null, function (Builder $builder) {
            return $builder->where('user_id', 6);
        });

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertTrue($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    public function testValidationPassesIfRuleChecksThatFactExistsAndBelongsToUserUsingFunction(): void
    {
        // Arrange
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
        $rule = (new ExistsEloquent(Fact::class))->query(function (Builder $builder) {
            return $builder->where('user_id', 6);
        });

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertTrue($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    /*
     * Test language support
     */

    public function testValidationMessageIsFromLaravelLanguageSupportWithParametersIfNoCustomValidationMessageIsSet(): void
    {
        // Arrange
        Lang::addLines([
            'validation.exists_model' => 'A :model with the :attribute ":value" does not exist.',
        ], Lang::getLocale(), 'modelValidationRules');
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = new ExistsEloquent(User::class);

        // Act
        $rule->passes('id', 2);

        // Assert
        $this->assertEquals('A user with the id "2" does not exist.', $rule->message());
    }

    public function testValidationMessageIsFromCustomValidationMessagePropertyIfItHasBeenSet(): void
    {
        // Arrange
        $customValidationMessage = 'The user does not exist!';
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = (new ExistsEloquent(User::class))
            ->withMessage($customValidationMessage);

        // Act
        $rule->passes('id', 3);

        // Assert
        $this->assertEquals($customValidationMessage, $rule->message());
    }

    public function testValidationMessageIsLaravelTranslationIfCustomTranslationIsSet(): void
    {
        // Arrange
        Lang::addLines([
            'validation.custom.user_already_exists' => 'A :model with the :attribute ":value" does not exist. / Test',
        ], Lang::getLocale());
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = (new ExistsEloquent(User::class))
            ->withCustomTranslation('validation.custom.user_already_exists');

        // Act
        $rule->passes('id', 2);

        // Assert
        $this->assertEquals('A user with the id "2" does not exist. / Test', $rule->message());
    }
}
