<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\PostResource;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;
}
