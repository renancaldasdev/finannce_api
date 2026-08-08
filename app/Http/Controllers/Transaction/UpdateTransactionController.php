<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\Transaction\UpdateTransactionService;

class UpdateTransactionController extends Controller
{
    public function __construct(
        private UpdateTransactionService $updateTransactionService
    ) {}

    public function __invoke(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $updateTransaction = $this->updateTransactionService->execute(
            user: $request->user(),
            transaction: $transaction,
            data: $request->validated(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Transação atualizada com sucesso!',
            'data' => new TransactionResource($updateTransaction),
        ]);
    }
}
