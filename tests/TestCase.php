<?php

namespace Korridor\LaravelModelValidationRules\Tests;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Korridor\LaravelModelValidationRules\ModelValidationServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->app->make(EloquentFactory::class)->load(__DIR__.'/TestClasses/Factories');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ModelValidationServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        $manager = new Manager();
        $manager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $manager->setEventDispatcher(new Dispatcher(new Container()));
        $manager->setAsGlobal();
        $manager->bootEloquent();
        $manager->schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('other_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('remember_token');
            $table->softDeletes();
            $table->timestamps();
        });
        $manager->schema()->create('facts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('type');
            $table->text('description');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
