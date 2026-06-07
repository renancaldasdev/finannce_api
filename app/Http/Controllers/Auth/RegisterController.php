<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private RegisterService $registerService,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $authData = $this->registerService->register($data['email'], $data['password'], $data['name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuário registrado com sucesso.',
            'data' => new AuthResource($authData),
        ]
        );
    }
}
