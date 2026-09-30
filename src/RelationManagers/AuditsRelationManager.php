<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Rembon\LaravelAuditor\Enums\ChangeEvent;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\ModelChangeResource;
use Rembon\LaravelAuditorFilament\Support\Access;

/**
 * Audit history of any record whose model uses the Auditable trait:
 *
 *     public static function getRelations(): array
 *     {
 *         return [AuditsRelationManager::class];
 *     }
 */
class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'Audit history';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return method_exists($ownerRecord, 'audits') && Access::allowed();
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ModelChangeResource::configureChangesTable($table, showModel: false)
            ->filters([
                SelectFilter::make('event')->options(collect(ChangeEvent::cases())->mapWithKeys(fn (ChangeEvent $event): array => [$event->value => $event->value])->all()),
            ]);
    }
}
