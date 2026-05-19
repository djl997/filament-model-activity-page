<?php

namespace Djl997\FilamentModelActivityPage\Tests\Fixtures;

use Djl997\FilamentModelActivityPage\Tests\Fixtures\PostResource;
use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test')
            ->resources([
                PostResource::class,
            ]);
    }
}
