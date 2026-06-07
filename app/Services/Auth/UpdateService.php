<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Log\AuthLoggerService;
use Illuminate\Support\Facades\Hash;

class UpdateService
{
    public function __construct(
        private readonly AuthLoggerService $logger
    ) {}

    public function update(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $this->logger->logSuccess($user, 'profile.update');

        return $user;
    }
}
