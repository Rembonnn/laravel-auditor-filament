<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Rembon\LaravelAuditor\Facades\Auditor;
use Rembon\LaravelAuditor\Models\Entry;
use Rembon\LaravelAuditor\Models\ModelChange;
use Rembon\LaravelAuditorFilament\AuditorPlugin;
use Rembon\LaravelAuditorFilament\Resources\Entries\EntryResource;
use Rembon\LaravelAuditorFilament\Resources\Entries\Pages\ListEntries;
use Rembon\LaravelAuditorFilament\Resources\Entries\Pages\ViewEntry;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\ModelChangeResource;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\Pages\ListModelChanges;
use Rembon\LaravelAuditorFilament\Resources\ModelChanges\Pages\ViewModelChange;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Post;
use Rembon\LaravelAuditorFilament\Widgets\AuditStatsWidget;

beforeEach(function (): void {
    $this->admin = admin();
    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('forbids the audit pages without the viewAuditor gate outside local', function (): void {
    $this->get(EntryResource::getUrl())->assertForbidden();
    $this->get(ModelChangeResource::getUrl())->assertForbidden();
});

it('follows the viewAuditor gate', function (): void {
    allowAuditor(false);
    $this->get(EntryResource::getUrl())->assertForbidden();

    allowAuditor();
    $this->get(EntryResource::getUrl())->assertOk()->assertSee('Audit entries');
    $this->get(ModelChangeResource::getUrl())->assertOk();
});

it('prefers the Auditor::auth() callback', function (): void {
    allowAuditor(false);
    Auditor::auth(fn (): bool => true);

    $this->get(EntryResource::getUrl())->assertOk();
});

it('is read-only', function (): void {
    allowAuditor();
    $post = Post::query()->create(['title' => 'Hello']);
    $entry = Entry::query()->firstOrFail();
    $change = ModelChange::query()->firstOrFail();

    expect(EntryResource::canViewAny())->toBeTrue()
        ->and(EntryResource::canView($entry))->toBeTrue()
        ->and(EntryResource::canCreate())->toBeFalse()
        ->and(EntryResource::canEdit($entry))->toBeFalse()
        ->and(EntryResource::canDelete($entry))->toBeFalse()
        ->and(EntryResource::canDeleteAny())->toBeFalse()
        ->and(ModelChangeResource::canCreate())->toBeFalse()
        ->and(ModelChangeResource::canEdit($change))->toBeFalse()
        ->and(ModelChangeResource::canDelete($change))->toBeFalse()
        ->and($post->exists)->toBeTrue();
});

it('lists and filters entries', function (): void {
    allowAuditor();
    Post::query()->create(['title' => 'A']);
    Post::query()->create(['title' => 'B']);
    $entries = Entry::query()->get();

    Livewire::test(ListEntries::class)
        ->assertCanSeeTableRecords($entries)
        ->filterTable('changes')
        ->assertCanSeeTableRecords($entries)
        ->filterTable('denied')
        ->assertCountTableRecords(0);
});

it('shows an entry', function (): void {
    allowAuditor();
    Post::query()->create(['title' => 'Hello']);
    $entry = Entry::query()->firstOrFail();

    Livewire::test(ViewEntry::class, ['record' => $entry->getRouteKey()])
        ->assertOk()
        ->assertSee($entry->ulid)
        ->assertSee($entry->correlation_id);
});

it('lists changes and filters them by event', function (): void {
    allowAuditor();
    $post = Post::query()->create(['title' => 'Hello']);
    $post->update(['title' => 'Bye']);

    $created = ModelChange::query()->where('event', 'created')->get();
    $updated = ModelChange::query()->where('event', 'updated')->get();

    Livewire::test(ListModelChanges::class)
        ->assertCanSeeTableRecords([...$created, ...$updated])
        ->filterTable('event', 'updated')
        ->assertCanSeeTableRecords($updated)
        ->assertCanNotSeeTableRecords($created);
});

it('shows the diff of a change', function (): void {
    allowAuditor();
    $post = Post::query()->create(['title' => 'Hello']);
    $post->update(['title' => 'Bye <b>bold</b>']);
    $change = ModelChange::query()->where('event', 'updated')->firstOrFail();

    Livewire::test(ViewModelChange::class, ['record' => $change->getRouteKey()])
        ->assertOk()
        ->assertSeeInOrder(['title', 'Hello', 'Bye &lt;b&gt;bold&lt;/b&gt;'], escape: false);
});

it('registers both resources and the widget by default', function (): void {
    expect(Filament::getPanel('admin')->getResources())->toContain(EntryResource::class, ModelChangeResource::class)
        ->and(Filament::getPanel('admin')->getWidgets())->toContain(AuditStatsWidget::class);
});

it('does not register what the plugin disables', function (): void {
    $panel = Panel::make()->id('other');

    AuditorPlugin::make()->entries(false)->statsWidget(false)->navigationGroup('Security')->register($panel);

    expect($panel->getResources())->toBe([ModelChangeResource::class])
        ->and($panel->getWidgets())->not->toContain(AuditStatsWidget::class);
});

it('uses the navigation group of the plugin', function (): void {
    expect(EntryResource::getNavigationGroup())->toBe('Audit');

    Filament::getPanel('admin')->getPlugin(AuditorPlugin::ID)->navigationGroup('Security');

    expect(EntryResource::getNavigationGroup())->toBe('Security')
        ->and(ModelChangeResource::getNavigationGroup())->toBe('Security');
});

it('keeps Gate checks on the panel user', function (): void {
    Gate::define('viewAuditor', fn ($user): bool => $user->is($this->admin));

    $this->get(EntryResource::getUrl())->assertOk();
});
