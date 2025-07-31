<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Roleseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'admin', 'description' => 'Admin'],
            ['role_name' => 'cashier', 'description' => 'kasir'],
            ['role_name' => 'chef', 'description' => 'chef'],
            ['role_name' => 'customer', 'description' => 'customer'],
        ];
      DB::table('roles')->insert($roles);
    }
}
