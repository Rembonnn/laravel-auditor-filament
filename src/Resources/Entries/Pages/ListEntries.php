<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Resources\Entries\Pages;

use Filament\Resources\Pages\ListRecords;
use Rembon\LaravelAuditorFilament\Resources\Entries\EntryResource;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;
}
