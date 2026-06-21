<?php

declare(strict_types=1);

namespace App\Services\Account;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class IndexAccountService
{
    public function execute(User $user): Collection
    {
        return $user->accounts()->latest()->get();
    }
}
