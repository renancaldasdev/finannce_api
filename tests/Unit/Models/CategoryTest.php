<?php

use App\Models\Category;
use App\Models\User;

test('category belongs to user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    expect($category->user->id)->toBe($user->id);
});

test('category soft deletes', function () {
    $category = Category::factory()->create();
    $categoryId = $category->id;

    $category->delete();

    expect(Category::find($categoryId))->toBeNull();
});

test('active scope returns only active categories', function () {
    $user = User::factory()->create();
    $activeCategory = Category::factory()->for($user)->create(['active' => true]);
    Category::factory()->for($user)->create(['active' => false]);

    $active = Category::where('user_id', $user->id)->active()->pluck('id')->toArray();

    expect($active)->toContain($activeCategory->id);
});

test('category has many transactions', function () {
    $category = Category::factory()->create();
    $transaction = \App\Models\Transaction::factory()->for($category)->create();

    expect($category->transactions()->pluck('id')->toArray())->toContain($transaction->id);
});

test('category type must be income or expense', function () {
    $category = Category::factory()->create(['type' => 'income']);

    expect($category->type)->toBeIn(['income', 'expense']);
});
