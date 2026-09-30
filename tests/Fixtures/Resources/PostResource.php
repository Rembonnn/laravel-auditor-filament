<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources;

use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Rembon\LaravelAuditorFilament\RelationManagers\AuditsRelationManager;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Post;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\Pages\ListPosts;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\Pages\ViewPost;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([TextEntry::make('title')]);
    }

    public static function getRelations(): array
    {
        return [AuditsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'view' => ViewPost::route('/{record}'),
        ];
    }
}
