<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\Database\Seeders\RoleSeeder;
use Workbench\Database\Seeders\TypeSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(TypeSeeder::class);
        $this->call(UserSeeder::class);
    }
}
