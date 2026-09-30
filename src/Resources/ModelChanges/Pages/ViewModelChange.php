<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Resources\ModelChanges\Pages;

use Filament\Resources\Pages\ViewRecord;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\ModelChangeResource;

class ViewModelChange extends ViewRecord
{
    protected static string $resource = ModelChangeResource::class;
}
