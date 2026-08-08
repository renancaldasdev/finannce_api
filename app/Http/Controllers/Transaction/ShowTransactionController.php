<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\Transaction\ShowTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowTransactionController extends Controller
{
    public function __construct(
        private readonly ShowTransactionService $showTransactionService
    ) {}

    public function __invoke(Request $request, Transaction $transaction): JsonResponse
    {
        $validatedCategory = $this->showTransactionService->execute(
            user: $request->user(),
            transaction: $transaction
        );

        return response()->json([
            'status' => 'success',
            'data' => new TransactionResource($validatedCategory),
        ]);
    }
}
