<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\Auth\LoginService;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private LoginService $loginService,
        private RegisterService $registerService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
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

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $authData = $this->loginService->login($data['email'], $data['password']);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuário logado com sucesso.',
            'data' => new AuthResource($authData),
        ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Token revogado e logout realizado com sucesso',
        ]);
    }
}
