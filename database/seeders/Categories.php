<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
 use Illuminate\Support\Facades\DB;
class Categories extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define categories
        $categories = [
            ['cat_name' => 'Makanan', 'description' => 'kategori makanan'],
            ['cat_name' => 'Minuman', 'description' => 'kategori minuman'],
            ['cat_name' => 'Snack', 'description' => 'kategori snack'],
        ];

        // Insert categories into the database
       DB::table('categories')->insert($categories);
    }
}
