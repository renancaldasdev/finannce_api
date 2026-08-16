<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\IndexTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\Transaction\IndexTransactionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexTransactionController extends Controller
{
    public function __construct(
        private IndexTransactionService $indexTransactionService
    ) {}

    public function __invoke(IndexTransactionRequest $request): AnonymousResourceCollection
    {
        $transactions = $this->indexTransactionService->execute(
            $request->user(),
            $request->validated()
        );

        return TransactionResource::collection($transactions);
    }
}
