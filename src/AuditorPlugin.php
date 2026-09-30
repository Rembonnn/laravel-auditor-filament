<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Rembon\LaravelAuditorFilament\Resources\Entries\EntryResource;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\ModelChangeResource;
use Rembon\LaravelAuditorFilament\Widgets\AuditStatsWidget;
use Throwable;

class AuditorPlugin implements Plugin
{
    public const string ID = 'laravel-auditor';

    protected bool $entries = true;

    protected bool $changes = true;

    protected bool $statsWidget = true;

    protected ?string $navigationGroup = 'Audit';

    final public function __construct() {}

    public static function make(): static
    {
        return new static;
    }

    /**
     * The plugin registered on the current panel, if any.
     */
    public static function current(): ?self
    {
        try {
            $plugin = Filament::getCurrentOrDefaultPanel()?->getPlugin(self::ID);
        } catch (Throwable) {
            return null;
        }

        return $plugin instanceof self ? $plugin : null;
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function entries(bool $condition = true): static
    {
        $this->entries = $condition;

        return $this;
    }

    public function changes(bool $condition = true): static
    {
        $this->changes = $condition;

        return $this;
    }

    public function statsWidget(bool $condition = true): static
    {
        $this->statsWidget = $condition;

        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function register(Panel $panel): void
    {
        $panel->resources(array_values(array_filter([
            $this->entries ? EntryResource::class : null,
            $this->changes ? ModelChangeResource::class : null,
        ])));

        if ($this->statsWidget) {
            $panel->widgets([AuditStatsWidget::class]);
        }
    }

    public function boot(Panel $panel): void {}
}
