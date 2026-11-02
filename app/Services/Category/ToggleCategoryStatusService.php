<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\Category;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ToggleCategoryStatusService
{
    public function execute(User $user, Category $category): Category
    {
        if ($category->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para alterar esta categoria.');
        }

        $category->update([
            'active' => ! $category->active,
        ]);

        return $category;
    }
}
