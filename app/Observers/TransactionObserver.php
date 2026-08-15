<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        if ($transaction->is_paid) {
            $this->processBalance(
                $transaction->account_id,
                $transaction->type,
                $transaction->amount
            );
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if (! $transaction->wasChanged(['amount', 'type', 'is_paid', 'account_id'])) {
            return;
        }

        if ($transaction->getOriginal('is_paid')) {
            $this->processBalance(
                $transaction->getOriginal('account_id'),
                $transaction->getOriginal('type'),
                $transaction->getOriginal('amount'),
                true
            );
        }

        if ($transaction->is_paid) {
            $this->processBalance(
                $transaction->account_id,
                $transaction->type,
                $transaction->amount
            );
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        if ($transaction->is_paid) {
            $this->processBalance(
                $transaction->account_id,
                $transaction->type,
                $transaction->amount,
                true
            );
        }
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }

    private function processBalance(int $accountId, string $type, int $amount, bool $reverse = false): void
    {
        $account = Account::find($accountId);

        if ($type === 'income') {
            $account->balance = $reverse ? $account->balance - $amount : $account->balance + $amount;
        } else {
            $account->balance = $reverse ? $account->balance + $amount : $account->balance - $amount;
        }

        $account->save();
    }
}
