<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class IndexTransactionService
{
    public function execute(User $user): Collection
    {
        return $user->transactions()->latest()->get();
    }
}
