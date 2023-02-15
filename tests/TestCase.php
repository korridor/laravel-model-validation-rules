<?php

declare(strict_types=1);

namespace Korridor\LaravelModelValidationRules\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Korridor\LaravelModelValidationRules\ModelValidationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ModelValidationServiceProvider::class,
        ];
    }

    protected function setUpDatabase(): void
    {
        $manager = new Manager();
        $manager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $manager->setEventDispatcher(new Dispatcher(new Container()));
        $manager->setAsGlobal();
        $manager->bootEloquent();
        $manager->schema()->create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('other_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('remember_token');
            $table->softDeletes();
            $table->timestamps();
        });
        $manager->schema()->create('facts', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('type');
            $table->text('description');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
