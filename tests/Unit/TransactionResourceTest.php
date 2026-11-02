<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Http\Resources\TransactionResource;
use Tests\TestCase;

class TransactionResourceTest extends TestCase
{
    public function test_transaction_resource_accessor_converts_cents(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();

        $transaction = Transaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->create(['amount' => 100.50]);

        $this->assertEquals(100.50, $transaction->amount);
    }

    public function test_transaction_resource_returns_correct_amount(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();

        $transaction = Transaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->create(['amount' => 100.50]);

        $resource = new TransactionResource($transaction);
        $data = $resource->resolve();

        $this->assertEquals(100.50, $data['amount']);
    }
}
