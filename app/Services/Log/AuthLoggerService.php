<?php

declare(strict_types=1);

namespace App\Services\Log;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthLoggerService
{
    public function logSuccess(User $user, string $method): void
    {
        Log::channel('auth')->info("{$method} efetuado com sucesso", [
            'action' => 'auth.'.$method,
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
