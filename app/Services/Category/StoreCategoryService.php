<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Models\Category;
use App\Models\User;

class StoreCategoryService
{
    public function execute(User $user, array $data): Category
    {
        $category = Category::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'type' => $data['type']
        ]);

        $category->refresh();

        return $category;
    }
}
