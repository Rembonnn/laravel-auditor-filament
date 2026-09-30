<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Resources\ModelChanges\Pages;

use Filament\Resources\Pages\ListRecords;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\ModelChangeResource;

class ListModelChanges extends ListRecords
{
    protected static string $resource = ModelChangeResource::class;
}
