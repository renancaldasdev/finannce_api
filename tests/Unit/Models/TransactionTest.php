<?php

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;

test('transaction amount is stored in cents and exposed as decimal', function () {
    $transaction = Transaction::factory()->create(['amount' => 50.00]);

    expect($transaction->amount)->toEqual(50.00);
    expect($transaction->getRawOriginal('amount'))->toBe(5000);
});

test('transaction belongs to account', function () {
    $account = Account::factory()->create();
    $transaction = Transaction::factory()->for($account)->create();

    expect($transaction->account->id)->toBe($account->id);
});

test('transaction belongs to category', function () {
    $category = Category::factory()->create();
    $transaction = Transaction::factory()->for($category)->create();

    expect($transaction->category->id)->toBe($category->id);
});

test('transaction soft deletes', function () {
    $transaction = Transaction::factory()->create();
    $transactionId = $transaction->id;

    $transaction->delete();

    expect(Transaction::find($transactionId))->toBeNull();
});

test('paid transactions filter correctly', function () {
    Transaction::factory()->create(['is_paid' => true]);
    Transaction::factory()->create(['is_paid' => false]);

    expect(Transaction::paid()->count())->toBe(1);
});
