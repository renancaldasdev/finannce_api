<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\Category;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ToogleCategoryStatusService
{
    public function execute(User $user, Category $category): Category
    {
        if ($category->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para excluir esta conta.');
        }

        $category->update([
            'active' => ! $category->active,
        ]);

        return $category;
    }
}
