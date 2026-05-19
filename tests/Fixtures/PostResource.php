<?php

namespace Djl997\FilamentModelActivityPage\Tests\Fixtures;

use Filament\Resources\Resource;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationLabel = 'Posts';

    public static function getPages(): array
    {
        return [
            'notes' => PostNotesPage::route('/{record}/notes'),
        ];
    }
}
