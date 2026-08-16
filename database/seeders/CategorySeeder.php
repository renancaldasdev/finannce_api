<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Nenhum usuário encontrado. Crie um usuário primeiro.');

            return;
        }

        foreach ($users as $user) {
            Category::firstOrCreate([
                'user_id' => $user->id,
                'name' => 'Transferência Enviada',
                'type' => 'expense',
            ]);

            Category::firstOrCreate([
                'user_id' => $user->id,
                'name' => 'Transferência Recebida',
                'type' => 'income',
            ]);
        }
    }
}
