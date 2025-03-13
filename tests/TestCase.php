<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Scheel\QueryRecorder\QueryRecorderServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Scheel\\QueryRecorder\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/migrations/test_table.php';
        $migration->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            QueryRecorderServiceProvider::class,
        ];
    }
}
