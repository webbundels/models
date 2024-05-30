<?php

namespace Workbench\Database\Seeders;

use Workbench\App\Models\Role;
use Workbench\App\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    protected $types = [
        'baba-voss', 'admin', 'teacher', 'student'
    ];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach($this->types as $type) {
            Role::create([
                'name' => $type
            ]);
        }
    }
}
