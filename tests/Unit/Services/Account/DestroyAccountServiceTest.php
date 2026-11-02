<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Account\DestroyAccountService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

test('destroy account service soft deletes account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $accountId = $account->id;
    $service = new DestroyAccountService();

    $service->execute($user, $account);

    expect(Account::find($accountId))->toBeNull();
});

test('destroy account service soft deletes preserve data with trashed', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $accountId = $account->id;
    $service = new DestroyAccountService();

    $service->execute($user, $account);

    $trashedAccount = Account::withTrashed()->find($accountId);
    expect($trashedAccount)->not->toBeNull();
    expect($trashedAccount->trashed())->toBeTrue();
});

test('destroy account service prevents unauthorized deletion', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->for($user1)->create();
    $service = new DestroyAccountService();

    expect(fn () => $service->execute($user2, $account))
        ->toThrow(AccessDeniedHttpException::class);
});

test('destroy account service does not permanently delete account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $accountId = $account->id;
    $service = new DestroyAccountService();

    $service->execute($user, $account);

    // Account should not exist in normal queries
    expect(Account::find($accountId))->toBeNull();

    // But should exist with soft deleted records
    $trashedAccount = Account::withTrashed()->find($accountId);
    expect($trashedAccount)->not->toBeNull();
});

test('destroy account service deletes only the authorized user account', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account1 = Account::factory()->for($user1)->create(['name' => 'Account 1']);
    $account2 = Account::factory()->for($user2)->create(['name' => 'Account 2']);
    $service = new DestroyAccountService();

    $service->execute($user1, $account1);

    expect(Account::find($account1->id))->toBeNull();
    expect(Account::find($account2->id))->not->toBeNull();
    expect(Account::find($account2->id)->name)->toBe('Account 2');
});

test('destroy account service sets deleted_at timestamp', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $service = new DestroyAccountService();

    $service->execute($user, $account);

    $trashedAccount = Account::withTrashed()->find($account->id);
    expect($trashedAccount->deleted_at)->not->toBeNull();
});

test('destroy account service prevents unauthorized access', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $account = Account::factory()->for($owner)->create(['name' => 'Protected']);
    $service = new DestroyAccountService();

    $exception = null;
    try {
        $service->execute($attacker, $account);
    } catch (AccessDeniedHttpException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect(Account::find($account->id))->not->toBeNull();
});

test('destroy account service deletes account without affecting other users data', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account1a = Account::factory()->for($user1)->create(['name' => 'A1']);
    $account1b = Account::factory()->for($user1)->create(['name' => 'A2']);
    $account2a = Account::factory()->for($user2)->create(['name' => 'B1']);
    $service = new DestroyAccountService();

    $service->execute($user1, $account1a);

    expect(Account::where('user_id', $user1->id)->count())->toBe(1);
    expect(Account::where('user_id', $user2->id)->count())->toBe(1);
    expect(Account::find($account1b->id))->not->toBeNull();
    expect(Account::find($account2a->id))->not->toBeNull();
});
