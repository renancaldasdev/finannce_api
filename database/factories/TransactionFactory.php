<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'account_id' => Account::factory(),
            'category_id' => Category::factory()->state(['type' => $type]),
            'amount' => fake()->numberBetween(100, 100000),
            'type' => $type,
            'date' => fake()->date(),
            'description' => fake()->sentence(),
            'is_paid' => false,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Transaction $transaction) {
            // Make sure user_id matches the account's user_id
            if (!$transaction->user_id && $transaction->account_id) {
                $transaction->user_id = $transaction->account->user_id;
            }
        })->afterCreating(function (Transaction $transaction) {
            // Update user_id if it doesn't match
            if ($transaction->user_id !== $transaction->account->user_id) {
                $transaction->update(['user_id' => $transaction->account->user_id]);
            }
        });
    }
}
