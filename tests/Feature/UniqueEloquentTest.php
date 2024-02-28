<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Tests\Feature;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [new UniqueEloquent(User::class)]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
    }

    public function testValidationPassesIfEntryIsSoftDeleted(): void
    {
        // Arrange
        $user = User::query()->create([
            'id' => 1,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [new UniqueEloquent(User::class)]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testValidationFailsIfEntryWithCorrectAttributeExists(): void
    {
        // Arrange
        User::query()->create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'id' => 2,
        ], [
            'id' => [new UniqueEloquent(User::class)]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('The resource already exists.', $messages['id'][0]);
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with other attribute
     */

    public function testValidationPassesIfEntryDoesNotExistInDatabaseUsingOtherAttribute(): void
    {
        // Arrange
        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [new UniqueEloquent(User::class, 'other_id')]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
    }

    public function testValidationPassesIfEntryIsSoftDeletedUsingOtherAttribute(): void
    {
        // Arrange
        $user = User::query()->create([
            'id' => 1,
            'other_id' => 3,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $user->delete();
        $validator = Validator::make([
            'id' => 3,
        ], [
            'id' => [new UniqueEloquent(User::class, 'other_id')]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(0, User::all());
    }

    public function testValidationFailsIfEntryWithCorrectAttributeExistsUsingOtherAttribute(): void
    {
        // Arrange
        User::query()->create([
            'id' => 2,
            'other_id' => 4,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'id' => 4,
        ], [
            'id' => [new UniqueEloquent(User::class, 'other_id')]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('The resource already exists.', $messages['id'][0]);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
    }

    /*
     * Tests with builder closure
     */

    public function testValidationFailsIfRuleChecksThatFactExistsAndBelongsToUserUsingConstructor(): void
    {
        // Arrange
        User::query()->create([
            'id' => 6,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        Fact::query()->create([
            'id' => 1,
            'user_id' => 6,
            'type' => 'type1',
            'description' => 'Long desc',
        ]);
        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [
                new UniqueEloquent(Fact::class, null, function (Builder $builder) {
                    return $builder->where('user_id', 6);
                })
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('The resource already exists.', $messages['id'][0]);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    public function testValidationFailsIfRuleChecksThatFactExistsAndBelongsToUserUsingFunction(): void
    {
        // Arrange
        User::query()->create([
            'id' => 6,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        Fact::query()->create([
            'id' => 1,
            'user_id' => 6,
            'type' => 'type1',
            'description' => 'Long desc',
        ]);
        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [
                (new UniqueEloquent(Fact::class))->query(function (Builder $builder) {
                    return $builder->where('user_id', 6);
                })
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('The resource already exists.', $messages['id'][0]);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(1, Fact::all());
    }

    /*
     * Tests for includeSoftDeleted
     */

    public function testValidationSucceedsIfSoftDeletedEntryDoesNotExistInDatabaseAndIncludeSoftDeletedFlagIsActive(): void
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

        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [(new UniqueEloquent(Fact::class))->includeSoftDeleted()]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(0, Fact::withTrashed()->get());
        $this->assertCount(0, Fact::all());
    }

    public function testValidationFailsIfSoftDeletedEntryDoesExistInDatabaseAndIncludeSoftDeletedFlagIsActive(): void
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
        $fact = Fact::create([
            'id' => 1,
            'user_id' => 6,
            'type' => 'type1',
            'description' => 'Long desc',
        ]);
        $fact->delete();

        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [(new UniqueEloquent(Fact::class))->includeSoftDeleted()]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('The resource already exists.', $messages['id'][0]);
        $this->assertCount(1, User::withTrashed()->get());
        $this->assertCount(1, User::all());
        $this->assertCount(1, Fact::withTrashed()->get());
        $this->assertCount(0, Fact::all());
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
        User::query()->create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'id' => 2,
        ], [
            'id' => [
                new UniqueEloquent(User::class)
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('A user with the id "2" already exists.', $messages['id'][0]);
    }

    public function testValidationMessageIsFromCustomValidationMessagePropertyIfItHasBeenSet(): void
    {
        // Arrange
        $customValidationMessage = 'The user is not unique!';
        User::query()->create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'id' => 2,
        ], [
            'id' => [
                (new UniqueEloquent(User::class))
                    ->withMessage($customValidationMessage)
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals($customValidationMessage, $messages['id'][0]);
    }

    public function testValidationMessageIsLaravelTranslationIfCustomTranslationIsSet(): void
    {
        // Arrange
        Lang::addLines([
            'validation.custom.user_already_exists' => 'A :model with the :attribute ":value" already exists. / Test',
        ], Lang::getLocale());
        User::query()->create([
            'id' => 2,
            'name' => 'Testname',
            'email' => 'name@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'id' => 2,
        ], [
            'id' => [
                (new UniqueEloquent(User::class))
                    ->withCustomTranslation('validation.custom.user_already_exists')
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertFalse($isValid);
        $this->assertEquals('A user with the id "2" already exists. / Test', $messages['id'][0]);
    }

    /*
     * Test ignore
     */

    public function testIgnoringEntryWithDefaultIdColumn(): void
    {
        // Arrange
        User::query()->create([
            'id' => 1,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name1@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        User::query()->create([
            'id' => 2,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name2@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'id' => 1,
        ], [
            'id' => [
                (new UniqueEloquent(User::class))->ignore(1)
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
    }

    public function testIgnoringEntryWithGivenIdColumn(): void
    {
        // Arrange
        User::query()->create([
            'id' => 1,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name1@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        User::query()->create([
            'id' => 2,
            'other_id' => null,
            'name' => 'Testname',
            'email' => 'name2@test.com',
            'password' => bcrypt('secret'),
            'remember_token' => Str::random(10),
        ]);
        $validator = Validator::make([
            'email' => 'name1@test.com',
        ], [
            'email' => [
                (new UniqueEloquent(User::class, 'email'))
                    ->ignore('name1@test.com', 'email')
            ]
        ]);

        // Act
        $isValid = $validator->passes();
        $messages = $validator->messages()->toArray();

        // Assert
        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('id', $messages);
    }
}
