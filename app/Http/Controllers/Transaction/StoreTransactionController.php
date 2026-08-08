<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\Transaction\StoreTransactionService;
use Illuminate\Http\JsonResponse;

class StoreTransactionController extends Controller
{
    public function __construct(
        private StoreTransactionService $storeTransactionService
    ) {}

    public function __invoke(StoreTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $transaction = $this->storeTransactionService->execute(
            user: $request->user(),
            data: $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Transação cadastrada com sucesso!',
            'data' => new TransactionResource($transaction),
        ], 201);
    }
}
