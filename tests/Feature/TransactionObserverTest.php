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
    expect($transaction->fresh()->getRawOriginal('amount'))->toBe(3000);
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

test('observer updates balance when transaction type changes', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 100]);
    $expenseCategory = Category::factory()->for($user)->create(['type' => 'expense']);
    $incomeCategory = Category::factory()->for($user)->create(['type' => 'income']);

    $transaction = Transaction::factory()->for($account)->for($expenseCategory)->create([
        'type' => 'expense',
        'amount' => 30.00,
        'is_paid' => true,
    ]);

    // Balance should be 70 after expense
    expect($account->fresh()->balance)->toEqual(70);

    // Change transaction type from expense to income
    $transaction->update([
        'type' => 'income',
        'category_id' => $incomeCategory->id,
    ]);

    // Balance should increase by 30 twice: revert the expense (-30) and apply income (+30)
    // 70 + 30 + 30 = 130
    expect($account->fresh()->balance)->toEqual(130);
});

test('observer updates balance when transaction moves to another account', function () {
    $user = User::factory()->create();
    $accountA = Account::factory()->for($user)->create(['balance' => 100]);
    $accountB = Account::factory()->for($user)->create(['balance' => 50]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    $transaction = Transaction::factory()->for($accountA)->for($category)->create([
        'type' => 'expense',
        'amount' => 20.00,
        'is_paid' => true,
    ]);

    // Balance should be 80 on account A
    expect($accountA->fresh()->balance)->toEqual(80);
    expect($accountB->fresh()->balance)->toEqual(50);

    // Move transaction to account B
    $transaction->update(['account_id' => $accountB->id]);

    // Account A should revert the expense: 80 + 20 = 100
    expect($accountA->fresh()->balance)->toEqual(100);
    // Account B should apply the expense: 50 - 20 = 30
    expect($accountB->fresh()->balance)->toEqual(30);
});
