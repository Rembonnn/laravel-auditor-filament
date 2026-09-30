<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Resources\ModelChanges;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Rembon\LaravelAuditor\Enums\ChangeEvent;
use Rembon\LaravelAuditor\Models\ModelChange;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\Pages\ListModelChanges;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\Pages\ViewModelChange;
use Rembon\LaravelAuditorFilament\Support\Format;
use Rembon\LaravelAuditorFilament\Support\ReadOnlyAuditResource;

class ModelChangeResource extends Resource
{
    use ReadOnlyAuditResource;

    protected static ?string $model = ModelChange::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Model changes';

    protected static ?string $modelLabel = 'model change';

    protected static ?string $slug = 'audit/changes';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return static::configureChangesTable($table, showModel: true)
            ->filters([
                SelectFilter::make('event')->options(self::eventOptions()),
                SelectFilter::make('auditable_type')->label('Model')->options(fn (): array => ModelChange::query()
                    ->distinct()->orderBy('auditable_type')->pluck('auditable_type')
                    ->mapWithKeys(fn (mixed $type): array => is_string($type) ? [$type => Format::type($type)] : [])->all()),
            ]);
    }

    /**
     * Shared by the resource and AuditsRelationManager.
     */
    public static function configureChangesTable(Table $table, bool $showModel): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns(array_values(array_filter([
                TextColumn::make('created_at')->label('When')->since()->dateTimeTooltip(),
                $showModel ? TextColumn::make('auditable')->label('Model')->state(fn (ModelChange $record): string => Format::morph($record->auditable_type, $record->auditable_id)) : null,
                TextColumn::make('event')->badge()->color(fn (ChangeEvent $state): string => self::eventColor($state)),
                TextColumn::make('attributes')->label('Attributes')->state(fn (ModelChange $record): string => implode(', ', $record->changedAttributes()))->limit(60)->placeholder('—'),
                TextColumn::make('user')->label('By')->state(fn (ModelChange $record): string => Format::morph($record->user_type, $record->user_id)),
            ])))
            ->recordActions([ViewAction::make()->schema(fn (Schema $schema): Schema => static::infolist($schema))]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Change')->columns(3)->schema([
                TextEntry::make('model')->state(fn (ModelChange $record): string => Format::morph($record->auditable_type, $record->auditable_id)),
                TextEntry::make('event')->badge()->color(fn (ChangeEvent $state): string => self::eventColor($state)),
                TextEntry::make('user')->label('By')->state(fn (ModelChange $record): string => Format::morph($record->user_type, $record->user_id)),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('correlation_id')->copyable()->fontFamily(FontFamily::Mono),
                TextEntry::make('ulid')->label('ULID')->copyable()->fontFamily(FontFamily::Mono),
            ]),
            Section::make('Diff')->schema([
                RepeatableEntry::make('diff')->hiddenLabel()
                    ->state(fn (ModelChange $record): array => Format::diff($record->old_values, $record->new_values))
                    ->table([TableColumn::make('Attribute'), TableColumn::make('Old'), TableColumn::make('New')])
                    ->schema([
                        TextEntry::make('attribute')->fontFamily(FontFamily::Mono),
                        TextEntry::make('old')->color('danger'),
                        TextEntry::make('new')->color('success'),
                    ]),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModelChanges::route('/'),
            'view' => ViewModelChange::route('/{record}'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function eventOptions(): array
    {
        return collect(ChangeEvent::cases())->mapWithKeys(fn (ChangeEvent $event): array => [$event->value => str($event->value)->replace('_', ' ')->ucfirst()->toString()])->all();
    }

    private static function eventColor(ChangeEvent $event): string
    {
        return match ($event) {
            ChangeEvent::Created, ChangeEvent::Restored => 'success',
            ChangeEvent::Updated => 'info',
            ChangeEvent::Deleted, ChangeEvent::ForceDeleted => 'danger',
        };
    }
}
