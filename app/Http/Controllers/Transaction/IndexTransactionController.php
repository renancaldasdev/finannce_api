<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\Transaction\IndexTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexTransactionController extends Controller
{
    public function __construct(
        private IndexTransactionService $indexTransactionService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = $this->indexTransactionService->execute($user);

        return response()->json([
            'satatus' => 'success',
            'data' => TransactionResource::collection($transactions),
        ]);
    }
}
