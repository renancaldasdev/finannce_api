<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\Account\StoreAccountService;
use Illuminate\Http\JsonResponse;

class StoreAccountController extends Controller
{
    public function __construct(
        private StoreAccountService $storeAccountService
    ) {}

    public function __invoke(StoreAccountRequest $request): JsonResponse
    {
        $data = $request->validated();

        $account = $this->storeAccountService->execute($request->user(), $data);

        return response()->json([
            'success' => true,
            'message' => 'Conta criada com sucesso!',
            'data' => new AccountResource($account),
        ], 201);
    }
}
