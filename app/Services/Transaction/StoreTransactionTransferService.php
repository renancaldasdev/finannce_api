<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StoreTransactionTransferService
{
    public function execute(User $user, array $data): void
    {
        $this->validateTransfer($user, $data);

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

    private function validateTransfer(User $user, array $data): void
    {
        $originAccount = Account::findOrFail($data['origin_account_id']);
        $destinationAccount = Account::findOrFail($data['destination_account_id']);

        if ($originAccount->user_id !== $user->id) {
            throw new BadRequestHttpException('A conta de origem não pertence a você.');
        }

        if ($destinationAccount->user_id !== $user->id) {
            throw new BadRequestHttpException('A conta de destino não pertence a você.');
        }

        if ($originAccount->id === $destinationAccount->id) {
            throw new BadRequestHttpException('A conta de origem e destino não podem ser a mesma.');
        }

        $amountInCents = (int) round($data['amount'] * 100);

        if ($originAccount->balance < $amountInCents) {
            throw new BadRequestHttpException(
                sprintf(
                    'Saldo insuficiente na conta de origem. Saldo disponível: R$ %.2f. Valor da transferência: R$ %.2f',
                    $originAccount->balance / 100,
                    $data['amount']
                )
            );
        }
    }
}
