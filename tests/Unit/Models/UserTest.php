<?php

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

test('user has many accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    expect($user->accounts()->pluck('id')->toArray())->toContain($account->id);
});

test('user has many categories', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    expect($user->categories()->pluck('id')->toArray())->toContain($category->id);
});

test('user has many transactions through accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($account)->create();

    expect($user->transactions()->pluck('id')->toArray())->toContain($transaction->id);
});
