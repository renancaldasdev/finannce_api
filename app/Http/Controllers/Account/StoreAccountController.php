<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\Account\StoreAccountService;

class StoreAccountController
{
    public function __construct(
        private StoreAccountService $storeAccountService
    ) {}

    public function __invoke(StoreAccountRequest $request)
    {
        $data = $request->validated();

        $account = $this->storeAccountService->execute($request->user(), $data);

        return response()->json([
            'success' => true,
            'message' => 'Conta criada com sucesso!',
            'data' => new AccountResource($account),
        ]);
    }
}
