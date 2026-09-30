<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Resources\Entries\Pages;

use Filament\Resources\Pages\ViewRecord;
use Rembon\LaravelAuditorFilament\Resources\Entries\EntryResource;

class ViewEntry extends ViewRecord
{
    protected static string $resource = EntryResource::class;
}
