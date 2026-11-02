<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

test('observer updates balance on paid income transaction create', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'income']);

    Transaction::factory()->for($account)->for($category)->create([
        'type' => 'income',
        'amount' => 50.00,
        'is_paid' => true,
    ]);

    expect($account->fresh()->balance)->toEqual(150);
});

test('observer updates balance on paid expense transaction create', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    Transaction::factory()->for($account)->for($category)->create([
        'type' => 'expense',
        'amount' => 30.00,
        'is_paid' => true,
    ]);

    expect($account->fresh()->balance)->toEqual(70);
});

test('observer does not update balance for unpaid transaction', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'income']);

    Transaction::factory()->for($account)->for($category)->create([
        'type' => 'income',
        'amount' => 50.00,
        'is_paid' => false,
    ]);

    expect($account->fresh()->balance)->toEqual(100);
});

test('observer updates balance on paid transaction delete', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    $transaction = Transaction::factory()->for($account)->for($category)->create([
        'type' => 'expense',
        'amount' => 30.00,
        'is_paid' => true,
    ]);

    // Balance should be 70 after expense
    expect($account->fresh()->balance)->toEqual(70);

    $transaction->delete();

    // Balance should revert to 100 after delete
    expect($account->fresh()->balance)->toEqual(100);
});

test('observer updates balance on amount change', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    $transaction = Transaction::factory()->for($account)->for($category)->create([
        'type' => 'expense',
        'amount' => 20.00,
        'is_paid' => true,
    ]);

    expect($account->fresh()->balance)->toEqual(80);

    // Update amount to 30
    $transaction->update(['amount' => 30.00]);

    expect($account->fresh()->balance)->toEqual(70);
});

test('observer handles is_paid status changes', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'income']);

    $transaction = Transaction::factory()->for($account)->for($category)->create([
        'type' => 'income',
        'amount' => 50.00,
        'is_paid' => false,
    ]);

    // Balance unchanged (unpaid)
    expect($account->fresh()->balance)->toEqual(100);

    // Mark as paid
    $transaction->update(['is_paid' => true]);

    expect($account->fresh()->balance)->toEqual(150);

    // Mark as unpaid
    $transaction->update(['is_paid' => false]);

    expect($account->fresh()->balance)->toEqual(100);
});
