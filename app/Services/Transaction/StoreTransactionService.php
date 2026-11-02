<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StoreTransactionService
{
    public function execute(
        User $user,
        array $data
    ): Transaction {
        $this->validateBalance($user, $data);

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

    private function validateBalance(User $user, array $data): void
    {
        if (! $data['is_paid'] || $data['type'] !== 'expense') {
            return;
        }

        $account = Account::findOrFail($data['account_id']);

        if ($account->user_id !== $user->id) {
            throw new BadRequestHttpException('A conta selecionada não pertence a você.');
        }

        if ($account->balance < $data['amount']) {
            throw new BadRequestHttpException(
                sprintf(
                    'Saldo insuficiente. Disponível: R$ %.2f. Valor da transação: R$ %.2f.',
                    $account->balance,
                    $data['amount']
                )
            );
        }
    }
}
