<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\Category;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DestroyCategoryService
{
    public function execute(User $user, Category $category): void
    {
        if ($category->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para excluir esta conta.');
        }

        $category->delete($category->id);
    }
}
