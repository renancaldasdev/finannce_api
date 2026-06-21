<?php

declare(strict_types=1);

namespace App\Services\Account;

use App\Models\Account;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DestroyAccountService
{
    public function execute(User $user, Account $account): void
    {
        if ($account->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para excluir esta conta.');
        }

        $account->delete($account->id);
    }
}
