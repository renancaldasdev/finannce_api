<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Account\IndexAccountService;
use Illuminate\Database\Eloquent\Collection;

test('index account service returns all accounts for user', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->count(3)->create();
    $service = new IndexAccountService();

    $accounts = $service->execute($user);

    expect($accounts)->toBeInstanceOf(Collection::class);
    expect($accounts)->toHaveCount(3);
});

test('index account service returns only user accounts', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Account::factory()->for($user1)->count(2)->create();
    Account::factory()->for($user2)->count(3)->create();

    $service = new IndexAccountService();
    $accounts = $service->execute($user1);

    expect($accounts)->toHaveCount(2);
    expect($accounts->every(fn ($a) => $a->user_id === $user1->id))->toBeTrue();
});

test('index account service returns empty collection when user has no accounts', function () {
    $user = User::factory()->create();
    $service = new IndexAccountService();

    $accounts = $service->execute($user);

    expect($accounts)->toHaveCount(0);
});

test('index account service orders accounts by id ascending', function () {
    $user = User::factory()->create();
    $account1 = Account::factory()->for($user)->create();
    $account2 = Account::factory()->for($user)->create();
    $account3 = Account::factory()->for($user)->create();
    $service = new IndexAccountService();

    $accounts = $service->execute($user);

    expect($accounts->pluck('id')->toArray())->toBe([
        $account1->id,
        $account2->id,
        $account3->id,
    ]);
});

test('index account service does not include soft deleted accounts', function () {
    $user = User::factory()->create();
    $account1 = Account::factory()->for($user)->create(['name' => 'Active']);
    $account2 = Account::factory()->for($user)->create(['name' => 'Deleted']);

    $account2->delete();

    $service = new IndexAccountService();
    $accounts = $service->execute($user);

    expect($accounts)->toHaveCount(1);
    expect($accounts->first()->name)->toBe('Active');
});

test('index account service returns collection with correct attributes', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create([
        'name' => 'Test Account',
        'type' => 'checking',
        'balance' => 1500.75,
    ]);

    $service = new IndexAccountService();
    $accounts = $service->execute($user);
    $account = $accounts->first();

    expect($account->name)->toBe('Test Account');
    expect($account->type)->toBe('checking');
    expect($account->balance)->toEqual(1500.75);
});

test('index account service handles multiple users independently', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    Account::factory()->for($user1)->count(2)->create();
    Account::factory()->for($user2)->count(5)->create();
    Account::factory()->for($user3)->count(1)->create();

    $service = new IndexAccountService();

    expect($service->execute($user1))->toHaveCount(2);
    expect($service->execute($user2))->toHaveCount(5);
    expect($service->execute($user3))->toHaveCount(1);
});

test('index account service returns data in consistent order', function () {
    $user = User::factory()->create();
    $ids = [];
    for ($i = 0; $i < 5; $i++) {
        $ids[] = Account::factory()->for($user)->create()->id;
    }

    $service = new IndexAccountService();
    $firstCall = $service->execute($user)->pluck('id')->toArray();
    $secondCall = $service->execute($user)->pluck('id')->toArray();

    expect($firstCall)->toBe($secondCall);
    expect($firstCall)->toBe($ids);
});
