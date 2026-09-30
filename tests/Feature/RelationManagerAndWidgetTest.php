<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Livewire\Livewire;
use Rembon\LaravelAuditorFilament\RelationManagers\AuditsRelationManager;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Post;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\Resources\Pages\ViewPost;
use Rembon\LaravelAuditorFilament\Widgets\AuditStatsWidget;

beforeEach(function (): void {
    $this->actingAs(admin());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('shows the audit history of a record', function (): void {
    allowAuditor();
    $post = Post::query()->create(['title' => 'Hello']);
    $post->update(['title' => 'Bye']);
    $other = Post::query()->create(['title' => 'Other']);

    Livewire::test(AuditsRelationManager::class, ['ownerRecord' => $post, 'pageClass' => ViewPost::class])
        ->assertOk()
        ->assertCanSeeTableRecords($post->audits)
        ->assertCanNotSeeTableRecords($other->audits)
        ->assertCountTableRecords(2);
});

it('hides the audit history without access', function (): void {
    $post = Post::query()->create(['title' => 'Hello']);

    expect(AuditsRelationManager::canViewForRecord($post, ViewPost::class))->toBeFalse();

    allowAuditor();

    expect(AuditsRelationManager::canViewForRecord($post, ViewPost::class))->toBeTrue();
});

it('shows the audit history on the view page', function (): void {
    allowAuditor();
    $post = Post::query()->create(['title' => 'Hello']);

    $this->get(ViewPost::getUrl(['record' => $post]))->assertOk()->assertSeeLivewire(AuditsRelationManager::class);
});

it('summarises the last 24 hours', function (): void {
    allowAuditor();
    Post::query()->create(['title' => 'A']);
    Post::query()->create(['title' => 'B'])->delete();

    expect(AuditStatsWidget::canView())->toBeTrue();

    Livewire::test(AuditStatsWidget::class)
        ->assertSeeInOrder(['Entries', '3', 'Denied abilities', '0', 'Failed', '0', 'Model changes', '3']);
});

it('hides the widget without access', function (): void {
    expect(AuditStatsWidget::canView())->toBeFalse();
});
