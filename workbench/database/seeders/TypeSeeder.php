<?php

namespace Workbench\Database\Seeders;

use Workbench\App\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TypeSeeder extends Seeder
{
    protected $types = [
        'Developer', 'Graphic designer', 'DevOps', 'Financial administrator'
    ];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach($this->types as $type) {
            Type::create([
                'name' => $type
            ]);
        }
    }
}
