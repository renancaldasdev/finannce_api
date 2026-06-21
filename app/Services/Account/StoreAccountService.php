<?php

declare(strict_types=1);

namespace App\Services\Account;

use App\Models\Account;
use App\Models\User;

class StoreAccountService
{
    public function execute(User $user, array $data): Account
    {
        return Account::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'balance' => $data['balance'] ?? 0,
        ]);

    }
}
