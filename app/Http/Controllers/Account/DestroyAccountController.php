<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Account\DestroyAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestroyAccountController extends Controller
{
    public function __construct(
        private readonly DestroyAccountService $destroyAccountService
    ) {}

    public function __invoke(Request $request, Account $account): JsonResponse
    {
        $this->destroyAccountService->execute($request->user(), $account);

        return response()->json([
            'status' => 'success',
            'message' => 'Conta excluída com sucesso!',
        ]);
    }
}
