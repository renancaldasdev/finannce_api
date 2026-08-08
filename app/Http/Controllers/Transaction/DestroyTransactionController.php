<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Transaction\DestroyTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestroyTransactionController extends Controller
{
    public function __construct(
        private readonly DestroyTransactionService $destroyTransactionService
    ) {}

    public function __invoke(Request $request, Transaction $transaction): JsonResponse
    {
        $this->destroyTransactionService->execute(
            user: $request->user(),
            transaction: $transaction
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Transação excluída com sucesso!',
        ]);
    }
}
