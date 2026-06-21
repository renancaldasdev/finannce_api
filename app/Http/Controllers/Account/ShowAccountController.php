<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\Account\ShowAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowAccountController extends Controller
{
    public function __construct(
        private readonly ShowAccountService $showAccountService
    ) {}

    public function __invoke(Request $request, Account $account): JsonResponse
    {
        $validatedAccount = $this->showAccountService->execute(
            $request->user(),
            $account
        );

        return response()->json([
            'status' => 'success',
            'data' => new AccountResource($validatedAccount),
        ]);
    }
}
