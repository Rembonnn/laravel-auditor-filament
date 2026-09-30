<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Rembon\LaravelAuditorFilament\Tests\Fixtures\User;
use Rembon\LaravelAuditorFilament\Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

function admin(): User
{
    return User::query()->create(['name' => 'Admin', 'email' => 'admin@example.com']);
}

function allowAuditor(bool $allowed = true): void
{
    Gate::define('viewAuditor', fn (?User $user = null): bool => $allowed);
}
