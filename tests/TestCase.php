<?php

namespace Djl997\FilamentModelActivityPage\Tests;

use Djl997\FilamentModelActivityPage\FilamentModelActivityPageServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ViewErrorBag;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Livewire 4's SupportValidation::render() calls getErrorBag() which reads
        // app('view')->getShared()['errors']. Testbench never runs ShareErrorsFromSession,
        // leaving it null and causing a TypeError. Share an empty bag unconditionally so
        // any Livewire request (including synthetic update requests) has the key available.
        app('view')->share('errors', new ViewErrorBag);

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Djl997\\FilamentModelActivityPage\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActionsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentModelActivityPageServiceProvider::class,
            \Djl997\FilamentModelActivityPage\Tests\Fixtures\TestPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('auth.providers.users.model', \Djl997\FilamentModelActivityPage\Tests\Fixtures\User::class);
        config()->set('session.driver', 'array');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../src/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
