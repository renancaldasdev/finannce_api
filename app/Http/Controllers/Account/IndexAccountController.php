<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Services\Account\IndexAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexAccountController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private IndexAccountService $indexAccountService
    ) {
        //
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $accounts = $this->indexAccountService->execute($user);

        return response()->json([
            'status' => 'success',
            'data' => AccountResource::collection($accounts),
        ]);
    }
}
