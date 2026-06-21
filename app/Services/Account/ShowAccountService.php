<?php

declare(strict_types=1);

namespace App\Services\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ShowAccountService
{
    public function execute(User $user, Account $account): Account
    {
        if ($account->user_id !== $user->id) {
            throw new AuthorizationException('Você não tem permissão para visualizar esta conta.');
        }

        return $account;
    }
}
