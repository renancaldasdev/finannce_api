<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\Account\CreateAccountService;

class CreateAccountController
{
    public function __construct(
        private CreateAccountService $createAccountService
    ) {}

    public function __invoke(CreateAccountRequest $request)
    {
        $data = $request->validated();

        $account = $this->createAccountService->createAccount($request->user(), $data);

        return response()->json([
            'success' => true,
            'message' => 'Conta criada com sucesso!',
            'data' => new AccountResource($account),
        ]);
    }
}
