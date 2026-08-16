<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class IndexCategoryService
{
    public function execute(User $user): Collection
    {
        return $user->categories()->orderBy('id', 'asc')->get();
    }
}
