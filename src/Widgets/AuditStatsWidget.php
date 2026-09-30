<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Date;
use Rembon\LaravelAuditor\Models\Entry;
use Rembon\LaravelAuditor\Models\ModelChange;
use Rembon\LaravelAuditorFilament\Resources\Entries\EntryResource;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\ModelChangeResource;
use Rembon\LaravelAuditorFilament\Support\Access;

class AuditStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Audit — last 24 hours';

    public static function canView(): bool
    {
        return Access::allowed();
    }

    protected function getStats(): array
    {
        $since = Date::now()->subDay();
        $entries = Entry::query()->where('created_at', '>=', $since);

        return [
            Stat::make('Entries', number_format((clone $entries)->count()))
                ->url(self::urlOf(EntryResource::class)),
            Stat::make('Denied abilities', number_format((int) (clone $entries)->sum('denied_abilities_count')))
                ->color('danger'),
            Stat::make('Failed', number_format((clone $entries)->where('failed', true)->count()))
                ->color('warning'),
            Stat::make('Model changes', number_format(ModelChange::query()->where('created_at', '>=', $since)->count()))
                ->url(self::urlOf(ModelChangeResource::class)),
        ];
    }

    /**
     * @param  class-string<EntryResource|ModelChangeResource>  $resource
     */
    private static function urlOf(string $resource): ?string
    {
        return in_array($resource, Filament::getCurrentOrDefaultPanel()?->getResources() ?? [], true) ? $resource::getUrl() : null;
    }
}
