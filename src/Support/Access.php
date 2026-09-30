<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Support;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Rembon\LaravelAuditor\Auditor;

/**
 * Same rules as the Laravel Auditor dashboard:
 *  1. Auditor::auth() callback, when set;
 *  2. otherwise the "viewAuditor" gate, when defined;
 *  3. otherwise only in the local environment.
 *
 * @internal
 */
final class Access
{
    public static function allowed(): bool
    {
        if ($callback = app(Auditor::class)->authCallback()) {
            return (bool) $callback(request());
        }

        if (Gate::has('viewAuditor')) {
            return Gate::forUser(Filament::auth()->user())->allows('viewAuditor');
        }

        return app()->isLocal();
    }
}
