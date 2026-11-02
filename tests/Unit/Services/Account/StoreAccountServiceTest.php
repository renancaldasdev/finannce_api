<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Account\StoreAccountService;

test('store account service creates account for authenticated user', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();

    $account = $service->execute($user, [
        'name' => 'Nubank',
        'type' => 'checking',
        'balance' => 1000.50,
    ]);

    expect($account)->toBeInstanceOf(Account::class);
    expect($account->user_id)->toBe($user->id);
    expect($account->name)->toBe('Nubank');
    expect($account->type)->toBe('checking');
    expect($account->balance)->toEqual(1000.50);
});

test('store account service creates account with zero initial balance', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();

    $account = $service->execute($user, [
        'name' => 'Savings',
        'type' => 'saving',
        'balance' => 0,
    ]);

    expect($account->balance)->toEqual(0);
});

test('store account service stores balance in cents', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();

    $account = $service->execute($user, [
        'name' => 'Investment',
        'type' => 'investment',
        'balance' => 150.75,
    ]);

    expect($account->getRawOriginal('balance'))->toBe(15075);
});

test('store account service creates account without initial balance', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();

    $account = $service->execute($user, [
        'name' => 'Test Account',
        'type' => 'checking',
    ]);

    expect($account->balance)->toEqual(0);
});

test('store account service handles decimal precision correctly', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();

    $account = $service->execute($user, [
        'name' => 'Precise',
        'type' => 'checking',
        'balance' => 99.99,
    ]);

    expect($account->balance)->toEqual(99.99);
    expect($account->getRawOriginal('balance'))->toBe(9999);
});

test('store account service persists data to database', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();

    $account = $service->execute($user, [
        'name' => 'Persistent',
        'type' => 'checking',
        'balance' => 500,
    ]);

    $retrieved = Account::find($account->id);

    expect($retrieved)->not->toBeNull();
    expect($retrieved->name)->toBe('Persistent');
    expect($retrieved->user_id)->toBe($user->id);
});

test('store account service creates different accounts for different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $service = new StoreAccountService();

    $account1 = $service->execute($user1, [
        'name' => 'Account',
        'type' => 'checking',
        'balance' => 100,
    ]);

    $account2 = $service->execute($user2, [
        'name' => 'Account',
        'type' => 'checking',
        'balance' => 200,
    ]);

    expect($account1->user_id)->toBe($user1->id);
    expect($account2->user_id)->toBe($user2->id);
    expect($account1->id)->not->toBe($account2->id);
});
