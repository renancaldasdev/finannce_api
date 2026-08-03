<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ShowCategoryService
{
    public function execute(User $user, Category $category): Category
    {
        if ($category->user_id !== $user->id) {
            throw new AuthorizationException('Você não tem permissão para visualizar esta conta.');
        }
        return $category;
    }
}
