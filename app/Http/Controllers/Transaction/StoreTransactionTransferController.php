<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionTransferRequest;
use App\Services\Transaction\StoreTransactionTransferService;
use Illuminate\Http\JsonResponse;

class StoreTransactionTransferController extends Controller
{
    public function __construct(
        private readonly StoreTransactionTransferService $storeTransactionTransfer
    ) {}

    public function __invoke(StoreTransactionTransferRequest $request): JsonResponse
    {
        $this->storeTransactionTransfer->execute(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transferência realizada com sucesso!',
        ], 201);
    }
}
