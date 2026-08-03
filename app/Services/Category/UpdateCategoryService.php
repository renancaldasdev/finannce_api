<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\Category;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateCategoryService
{
    public function execute(User $user, Category $category, array $data): Category
    {
        if ($category->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Você não tem permissão para alterar esta conta.');
        }

        $category->name = $data['name'];
        $category->type = $data['type'];
        $category->description = $data['description'] ?? $category->description;

        $category->save();

        return $category;
    }
}
