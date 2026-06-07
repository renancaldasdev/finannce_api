<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private LoginService $loginService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $authData = $this->loginService->login($data['email'], $data['password']);

        return response()->json([
            'status' => 'success',
            'data' => new LoginResource($authData),
        ]
        );
    }
}
