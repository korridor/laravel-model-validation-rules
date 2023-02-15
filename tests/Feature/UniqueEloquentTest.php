<?php

declare(strict_types=1);

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

    public function testValidationPassesWhenIfEntryDoesNotExistInDatabase(): void
    {
        // Arrange
        $rule = new UniqueEloquent(User::class);

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertTrue($isValid);
    }

    public function testValidationPassesIfEntryIsSoftDeleted(): void
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
        $rule = new UniqueEloquent(User::class);

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertTrue($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testValidationFailsIfEntryWithCorrectAttributeExists(): void
    {
        // Arrange
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = new UniqueEloquent(User::class);

        // Act
        $isValid = $rule->passes('id', 2);

        // Assert
        $this->assertFalse($isValid);
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with other attribute
     */

    public function testValidationPassesIfEntryDoesNotExistInDatabaseUsingOtherAttribute(): void
    {
        // Arrange
        $rule = new UniqueEloquent(User::class, 'other_id');

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertTrue($isValid);
    }

    public function testValidationPassesIfEntryIsSoftDeletedUsingOtherAttribute(): void
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
        $rule = new UniqueEloquent(User::class, 'other_id');

        // Act
        $isValid = $rule->passes('id', 3);

        // Assert
        $this->assertTrue($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testValidationFailsIfEntryWithCorrectAttributeExistsUsingOtherAttribute(): void
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
        $rule = new UniqueEloquent(User::class, 'other_id');

        // Act
        $isValid = $rule->passes('id', 4);

        // Assert
        $this->assertFalse($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with builder closure
     */

    public function testValidationFailsIfRuleChecksThatFactExistsAndBelongsToUserUsingConstructor(): void
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
        $rule = new UniqueEloquent(Fact::class, null, function (Builder $builder) {
            return $builder->where('user_id', 6);
        });

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertFalse($isValid);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    public function testValidationFailsIfRuleChecksThatFactExistsAndBelongsToUserUsingFunction(): void
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
        $rule = (new UniqueEloquent(Fact::class))->query(function (Builder $builder) {
            return $builder->where('user_id', 6);
        });

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertFalse($isValid);
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
            'validation.unique_model' => 'A :model with the :attribute ":value" already exists.',
        ], Lang::getLocale(), 'modelValidationRules');
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = new UniqueEloquent(User::class);

        // Act
        $rule->passes('id', 2);

        // Assert
        $this->assertEquals('A user with the id "2" already exists.', $rule->message());
    }

    public function testValidationMessageIsFromCustomValidationMessagePropertyIfItHasBeenSet(): void
    {
        // Arrange
        $customValidationMessage = 'The user is not unique!';
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = (new UniqueEloquent(User::class))
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
            'validation.custom.user_already_exists' => 'A :model with the :attribute ":value" already exists. / Test',
        ], Lang::getLocale());
        User::create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $rule = (new UniqueEloquent(User::class))
                    ->withCustomTranslation('validation.custom.user_already_exists');

        // Act
        $rule->passes('id', 2);

        // Assert
        $this->assertEquals('A user with the id "2" already exists. / Test', $rule->message());
    }

    /*
     * Test ignore
     */

    public function testIgnoringEntryWithDefaultIdColumn(): void
    {
        // Arrange
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
        $rule = (new UniqueEloquent(User::class))->ignore(1);

        // Act
        $isValid = $rule->passes('id', 1);

        // Assert
        $this->assertTrue($isValid);
    }

    public function testIgnoringEntryWithGivenIdColumn(): void
    {
        // Arrange
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
        $rule = (new UniqueEloquent(User::class, 'email'))->ignore('name1@test.com', 'email');

        // Act
        $isValid = $rule->passes('email', 'name1@test.com');

        // Assert
        $this->assertTrue($isValid);
    }
}
