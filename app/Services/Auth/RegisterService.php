<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Log\AuthLoggerService;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function __construct(private AuthLoggerService $loggerService) {}

    public function register(string $email, string $password, string $name): array
    {
        $user = User::create([
            'email' => $email,
            'password' => Hash::make($password),
            'name' => $name,
        ]);

        $this->loggerService->logSuccess($user, 'register.success');

        $token = $user->createToken($user->name)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
