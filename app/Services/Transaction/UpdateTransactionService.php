<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UpdateTransactionService
{
    public function execute(User $user, Transaction $transaction, array $data): Transaction
    {
        if ($transaction->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para alterar esta transação.');
        }

        if ($transaction->is_paid) {
            throw new BadRequestHttpException('Transações já pagas não podem ser alteradas.');
        }

        $this->validateBalance($user, $transaction, $data);

        $transaction->account_id = $data['account_id'];
        $transaction->category_id = $data['category_id'];
        $transaction->amount = (int) round($data['amount'] * 100);
        $transaction->type = $data['type'];
        $transaction->date = $data['date'];
        $transaction->is_paid = $data['is_paid'];
        $transaction->description = $data['description'] ?? $transaction->description;

        $transaction->save();

        return $transaction;
    }

    private function validateBalance(User $user, Transaction $transaction, array $data): void
    {
        $originalWasPaid = $transaction->getOriginal('is_paid');
        $newWillBePaid = $data['is_paid'];
        $originalType = $transaction->getOriginal('type');
        $newType = $data['type'];
        $originalAmount = $transaction->getOriginal('amount');
        $newAmountInCents = (int) round($data['amount'] * 100);

        $account = Account::findOrFail($data['account_id']);

        if ($account->user_id !== $user->id) {
            throw new BadRequestHttpException('A conta selecionada não pertence a você.');
        }

        $expectedBalance = $account->balance;

        if ($originalWasPaid && ($originalType === 'expense' || $originalAmount !== $newAmountInCents || $transaction->account_id !== $data['account_id'])) {
            $expectedBalance += $originalAmount;
        }

        if ($newWillBePaid && $newType === 'expense') {
            $expectedBalance -= $newAmountInCents;
        }

        if ($expectedBalance < 0) {
            throw new BadRequestHttpException(
                sprintf(
                    'Saldo insuficiente. Disponível após reversão: R$ %.2f. Transação: R$ %.2f',
                    $expectedBalance / 100,
                    $data['amount']
                )
            );
        }
    }
}
