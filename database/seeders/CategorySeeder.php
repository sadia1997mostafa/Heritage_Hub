<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Handmade Crafts', 'Textiles', 'Woodwork',
            'Pottery', 'Jewelry', 'Art Paintings'
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(['slug'=>Str::slug($c)], ['name'=>$c]);
        }
    }
}
