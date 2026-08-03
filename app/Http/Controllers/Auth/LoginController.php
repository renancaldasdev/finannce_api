<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $authData = $this->loginService->login($data['email'], $data['password']);

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Usuário logado com sucesso.',
                'data' => new AuthResource($authData),
            ]
        );
    }
}
