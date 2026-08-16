<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IndexTransactionService
{
    public function execute(User $user, array $filters): LengthAwarePaginator
    {
        $query = $user->transactions()->with(['account', 'category']);

        $query->when($filters['start_date'] ?? null, function ($q, $startDate) {
            $q->whereDate('date', '>=', $startDate);
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) {
            $q->whereDate('date', '<=', $endDate);
        });

        $query->when($filters['type'] ?? null, function ($q, $type) {
            $q->where('type', $type);
        });

        $query->when($filters['account_id'] ?? null, function ($q, $accountId) {
            $q->where('account_id', $accountId);
        });

        $query->when($filters['category_id'] ?? null, function ($q, $categoryId) {
            $q->where('category_id', $categoryId);
        });

        $perPage = $filters['per_page'] ?? 20;

        return $query->latest('date')->latest('id')->paginate((int) $perPage);
    }
}
