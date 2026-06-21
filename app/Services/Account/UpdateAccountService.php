<?php

declare(strict_types=1);

namespace App\Services\Account;

use App\Models\Account;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateAccountService
{
    public function execute(User $user, Account $account, array $data): Account
    {
        if ($account->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para alterar esta conta.');
        }

        $account->name = $data['name'];
        $account->type = $data['type'];
        $account->save();

        return $account;
    }
}
