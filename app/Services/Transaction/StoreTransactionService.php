<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

class StoreTransactionService
{
    public function execute(
        User $user,
        array $data
    ): Transaction {
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'account_id'  => $data['account_id'],
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'type' => $data['type'],
            'date' => $data['date'],
            'description' => $data['description'],
            'is_paid'     => $data['is_paid'],
        ]);

        $transaction->refresh();

        return $transaction;
    }
}
