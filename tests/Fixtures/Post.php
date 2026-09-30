<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Rembon\LaravelAuditor\Traits\Auditable;

class Post extends Model
{
    use Auditable;

    protected $guarded = [];
}
