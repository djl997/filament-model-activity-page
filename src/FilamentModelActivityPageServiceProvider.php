<?php

namespace Djl997\FilamentModelActivityPage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentModelActivityPageServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-model-activity-page';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigration('create_activities_table')
            ->runsMigrations()
            ->hasTranslations()
            ->hasViews();
    }
}
