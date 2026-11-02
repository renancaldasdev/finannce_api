<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        if ($transaction->is_paid) {
            $this->processBalance(
                $transaction->account_id,
                $transaction->type,
                (int)($transaction->amount * 100)
            );
        }
    }

    public function updated(Transaction $transaction): void
    {
        if (! $transaction->wasChanged(['amount', 'type', 'is_paid', 'account_id'])) {
            return;
        }

        if ($transaction->getOriginal('is_paid')) {
            $originalAmount = $transaction->getOriginal('amount');
            $this->processBalance(
                $transaction->getOriginal('account_id'),
                $transaction->getOriginal('type'),
                is_numeric($originalAmount) ? (int)($originalAmount * 100) : 0,
                true
            );
        }

        if ($transaction->is_paid) {
            $this->processBalance(
                $transaction->account_id,
                $transaction->type,
                (int)($transaction->amount * 100)
            );
        }
    }

    public function deleted(Transaction $transaction): void
    {
        if ($transaction->is_paid) {
            $this->processBalance(
                $transaction->account_id,
                $transaction->type,
                (int)($transaction->amount * 100),
                true
            );
        }
    }

    public function restored(Transaction $transaction): void
    {
        //
    }

    public function forceDeleted(Transaction $transaction): void
    {
        //
    }

    private function processBalance(int $accountId, string $type, int $amount, bool $reverse = false): void
    {
        $account = Account::find($accountId);

        $isAddition = ($type === 'income' && ! $reverse) || ($type === 'expense' && $reverse);

        if ($isAddition) {
            $account->increment('balance', $amount);
        } else {
            $account->decrement('balance', $amount);
        }
    }
}
