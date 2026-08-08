<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ShowTransactionService
{
    public function execute(User $user, Transaction $transaction): Transaction
    {
        if ($transaction->user_id !== $user->id) {
            throw new AuthorizationException('Você não tem permissão para visualizar esta conta.');
        }

        return $transaction;
    }
}
