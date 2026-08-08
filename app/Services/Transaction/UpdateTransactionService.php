<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateTransactionService
{
    public function execute(User $user, Transaction $transaction, array $data): Transaction
    {
        if ($transaction->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para alterar esta conta.');
        }

        $transaction->account_id = $data['account_id'];
        $transaction->category_id = $data['category_id'];
        $transaction->amount = $data['amount'];
        $transaction->type = $data['type'];
        $transaction->date = $data['date'];
        $transaction->is_paid = $data['is_paid'];
        $transaction->description = $data['description'] ?? $transaction->description;

        $transaction->save();

        return $transaction;
    }
}
