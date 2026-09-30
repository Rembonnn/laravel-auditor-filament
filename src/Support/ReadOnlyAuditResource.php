<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Support;

use BackedEnum;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Rembon\LaravelAuditorFilament\AuditorPlugin;
use UnitEnum;

/**
 * Audit records can be viewed (by whoever may view the Auditor dashboard)
 * but never created, edited or deleted from the panel.
 *
 * @internal
 */
trait ReadOnlyAuditResource
{
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $action = $action instanceof BackedEnum ? (string) $action->value : ($action instanceof UnitEnum ? $action->name : $action);

        return in_array($action, ['viewAny', 'view'], true) && Access::allowed()
            ? Response::allow()
            : Response::deny();
    }

    public static function getNavigationGroup(): ?string
    {
        return AuditorPlugin::current()?->getNavigationGroup() ?? 'Audit';
    }
}
