<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\UpdateService;
use Illuminate\Http\JsonResponse;

class UpdateController
{
    public function __construct(
        private UpdateService $updateService
    ) {}

    public function __invoke(UpdateRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = $this->updateService->update(
            $request->user(),
            $validatedData
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Alterações realizadas com sucesso.',
            'data' => new UserResource($user),
        ]
        );
    }
}
