<?php

use App\Models\Account;
use App\Models\User;

test('account balance is stored in cents and exposed as decimal', function () {
    $account = Account::factory()->create(['balance' => 150.75]);

    expect($account->balance)->toEqual(150.75);
    expect($account->getRawOriginal('balance'))->toBe(15075);
});

test('account belongs to user', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    expect($account->user->id)->toBe($user->id);
});

test('account soft deletes', function () {
    $account = Account::factory()->create();
    $accountId = $account->id;

    $account->delete();

    expect(Account::find($accountId))->toBeNull();
    expect(Account::withTrashed()->find($accountId))->not->toBeNull();
});

test('account has many transactions', function () {
    $account = Account::factory()->create();
    $transaction = \App\Models\Transaction::factory()->for($account)->create();

    expect($account->transactions()->pluck('id')->toArray())->toContain($transaction->id);
});
