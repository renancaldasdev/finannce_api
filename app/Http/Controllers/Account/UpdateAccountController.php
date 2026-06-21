<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\Account\UpdateAccountService;
use Illuminate\Http\JsonResponse;

class UpdateAccountController extends Controller
{
    public function __construct(
        private readonly UpdateAccountService $updateAccountService
    ) {}

    public function __invoke(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $updatedAccount = $this->updateAccountService->execute(
            $request->user(),
            $account,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Conta atualizada com sucesso!',
            'data' => new AccountResource($updatedAccount),
        ]);
    }
}
