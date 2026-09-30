<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Resources\Entries;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Rembon\LaravelAuditor\Enums\EntryType;
use Rembon\LaravelAuditor\Models\Entry;
use Rembon\LaravelAuditorFilament\Resources\Entries\Pages\ListEntries;
use Rembon\LaravelAuditorFilament\Resources\Entries\Pages\ViewEntry;
use Rembon\LaravelAuditorFilament\Support\Format;
use Rembon\LaravelAuditorFilament\Support\ReadOnlyAuditResource;

class EntryResource extends Resource
{
    use ReadOnlyAuditResource;

    protected static ?string $model = Entry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Audit entries';

    protected static ?string $modelLabel = 'audit entry';

    protected static ?string $slug = 'audit/entries';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('started_at')->label('When')->since()->dateTimeTooltip()->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('id', $direction === 'asc' ? 'asc' : 'desc')),
                TextColumn::make('type')->badge()->color(fn (EntryType $state): string => match ($state) {
                    EntryType::Http => 'info',
                    EntryType::Job => 'warning',
                    EntryType::Command => 'gray',
                    EntryType::Other => 'gray',
                }),
                TextColumn::make('http_method')->label('Method')->placeholder('—')->toggleable(),
                TextColumn::make('name')->searchable()->limit(60)->placeholder('—'),
                TextColumn::make('user')->label('User')->state(fn (Entry $record): string => Format::morph($record->user_type, $record->user_id)),
                TextColumn::make('status_code')->label('Status')->badge()->placeholder('—')->color(fn (?int $state): string => match (true) {
                    $state === null => 'gray',
                    $state >= 500 => 'danger',
                    $state >= 400 => 'warning',
                    default => 'success',
                }),
                TextColumn::make('duration_ms')->label('Duration')->suffix(' ms')->numeric()->placeholder('—')->toggleable(),
                TextColumn::make('denied_abilities_count')->label('Denied')->numeric()->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray'),
                TextColumn::make('model_changes_count')->label('Changes')->numeric(),
            ])
            ->filters([
                SelectFilter::make('type')->options(collect(EntryType::cases())->mapWithKeys(fn (EntryType $type): array => [$type->value => ucfirst($type->value)])->all()),
                TernaryFilter::make('failed'),
                Filter::make('denied')->label('With denied abilities')->query(fn (Builder $query): Builder => $query->where('denied_abilities_count', '>', 0)),
                Filter::make('changes')->label('With model changes')->query(fn (Builder $query): Builder => $query->where('model_changes_count', '>', 0)),
            ])
            ->recordActions([ViewAction::make()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Entry')->columns(3)->schema([
                TextEntry::make('type')->badge(),
                TextEntry::make('name')->placeholder('—'),
                TextEntry::make('user')->state(fn (Entry $record): string => Format::morph($record->user_type, $record->user_id).($record->guard ? " ({$record->guard})" : '')),
                TextEntry::make('http_method')->label('Method')->placeholder('—'),
                TextEntry::make('url')->label('URL')->placeholder('—')->columnSpan(2)->fontFamily(FontFamily::Mono),
                TextEntry::make('status_code')->label('Status')->placeholder('—'),
                TextEntry::make('duration_ms')->label('Duration')->suffix(' ms')->placeholder('—'),
                IconEntry::make('failed')->boolean(),
                TextEntry::make('route_action')->label('Action')->placeholder('—')->fontFamily(FontFamily::Mono),
                TextEntry::make('ip')->label('IP')->placeholder('—'),
                TextEntry::make('user_agent')->placeholder('—')->columnSpan(2),
                TextEntry::make('os_user')->label('OS user')->placeholder('—'),
                TextEntry::make('hostname')->placeholder('—'),
                TextEntry::make('started_at')->dateTime(),
                TextEntry::make('completed_at')->dateTime()->placeholder('Still running'),
                TextEntry::make('ulid')->label('ULID')->copyable()->fontFamily(FontFamily::Mono),
                TextEntry::make('correlation_id')->copyable()->fontFamily(FontFamily::Mono),
            ]),
            Section::make('Abilities')->visible(fn (Entry $record): bool => (bool) $record->abilities)->schema([
                RepeatableEntry::make('abilities')->hiddenLabel()
                    ->table([TableColumn::make('Ability'), TableColumn::make('Result'), TableColumn::make('Arguments'), TableColumn::make('Count')])
                    ->schema([
                        TextEntry::make('ability')->fontFamily(FontFamily::Mono),
                        IconEntry::make('result')->boolean(),
                        TextEntry::make('arguments')->formatStateUsing(fn (mixed $state): string => Format::value($state))->listWithLineBreaks()->placeholder('—'),
                        TextEntry::make('count')->default(1),
                    ]),
            ]),
            Section::make('Mails & notifications')
                ->visible(fn (Entry $record): bool => (bool) $record->mails || (bool) $record->notifications)
                ->schema([
                    TextEntry::make('mails')->state(fn (Entry $record): string => Format::json($record->mails))->fontFamily(FontFamily::Mono),
                    TextEntry::make('notifications')->state(fn (Entry $record): string => Format::json($record->notifications))->fontFamily(FontFamily::Mono),
                ]),
            Section::make('Data')->collapsed()->schema([
                TextEntry::make('models_accessed')->label('Models read')->state(fn (Entry $record): string => Format::json($record->models_accessed))->fontFamily(FontFamily::Mono),
                TextEntry::make('input')->state(fn (Entry $record): string => Format::json($record->input))->fontFamily(FontFamily::Mono),
                TextEntry::make('properties')->state(fn (Entry $record): string => Format::json($record->properties))->fontFamily(FontFamily::Mono),
                TextEntry::make('tags')->badge()->placeholder('—'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntries::route('/'),
            'view' => ViewEntry::route('/{record}'),
        ];
    }
}
