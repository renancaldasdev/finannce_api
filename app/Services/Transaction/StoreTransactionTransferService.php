<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreTransactionTransferService
{
    public function execute(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $data['origin_account_id'],
                'category_id' => $data['origin_category_id'],
                'amount' => $data['amount'],
                'type' => 'expense',
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Transferência enviada',
                'is_paid' => true,
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $data['destination_account_id'],
                'category_id' => $data['destination_category_id'],
                'amount' => $data['amount'],
                'type' => 'income',
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Transferência recebida',
                'is_paid' => true,
            ]);
        });
    }
}
