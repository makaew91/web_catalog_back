<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Электроника', 'description' => 'Гаджеты, компьютеры, аксессуары'],
            ['name' => 'Книги', 'description' => 'Художественная и техническая литература'],
            ['name' => 'Одежда', 'description' => 'Одежда и обувь для всей семьи'],
            ['name' => 'Дом и сад', 'description' => 'Товары для дома, дачи и интерьера'],
            ['name' => 'Спорт', 'description' => 'Снаряжение и спортивная экипировка'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
