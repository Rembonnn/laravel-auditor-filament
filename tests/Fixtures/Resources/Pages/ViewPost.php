<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\PostResource;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;
}
