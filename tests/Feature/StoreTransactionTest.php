<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;

class StoreTransactionTest extends TestCase
{
    public function test_store_transaction_returns_201(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['balance' => 500.00]);
        $category = Category::factory()->for($user)->create(['type' => 'expense']);

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 100.50,
            'type' => 'expense',
            'date' => '2026-11-02',
            'description' => 'Test transaction',
            'is_paid' => true,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $this->assertEquals(100.50, $response->json('data.amount'));
    }
}
