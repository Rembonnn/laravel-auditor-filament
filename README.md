# Laravel Auditor for Filament

A [Filament](https://filamentphp.com) v5 panel plugin for [Laravel Auditor](https://github.com/Rembonnn/laravel-auditor):

- **Audit entries** — every request, job and command: user, route, status, duration,
  abilities checked, mails, notifications and the models it read.
- **Model changes** — every created / updated / deleted / restored record with an old → new diff.
- **Audit history** relation manager for any resource whose model uses the `Auditable` trait.
- **Stats widget** — entries, denied abilities, failures and changes in the last 24 hours.

Everything is read-only. Access follows the same rules as the Laravel Auditor dashboard:
the `Auditor::auth()` callback, otherwise the `viewAuditor` gate, otherwise the local
environment only.

Requires PHP 8.3+, Laravel 12 or 13, Filament 5 and Laravel Auditor 3.

## Installation

```bash
composer require rembon/laravel-auditor-filament
```

Register the plugin in your panel provider:

```php
use Rembon\LaravelAuditorFilament\AuditorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            AuditorPlugin::make()
                ->navigationGroup('Audit') // default
                ->entries()                // Audit entries resource
                ->changes()                // Model changes resource
                ->statsWidget()            // dashboard widget
        );
}
```

Allow your team in production with the gate you already use for the Auditor dashboard:

```php
Gate::define('viewAuditor', fn (User $user): bool => $user->isAdmin());
```

## Audit history on your own resources

```php
use Rembon\LaravelAuditorFilament\RelationManagers\AuditsRelationManager;

class PostResource extends Resource
{
    public static function getRelations(): array
    {
        return [
            AuditsRelationManager::class,
        ];
    }
}
```

The model must use `Rembon\LaravelAuditor\Traits\Auditable`.

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE](LICENSE).
