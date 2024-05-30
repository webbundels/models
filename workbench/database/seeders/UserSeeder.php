<?php

namespace Workbench\Database\Seeders;

use Illuminate\Support\Arr;
use Workbench\App\Models\Role;
use Workbench\App\Models\Type;
use Workbench\App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    protected $users = [
        [
            'name' => 'Nick van Leeuwen',
            'email' => 'nick@webbundels.nl', 
            'type_id' => 'Developer',
            'roles' => ['baba-voss', 'admin'],
            'coins' => 100
        ],

        [
            'name' => 'Stephan Baggerman',
            'email' => 'stephan@webbundels.nl', 
            'type_id' => 'Graphic designer',
            'roles' => ['admin', 'teacher'],
            'coins' => 50
        ],

        [
            'name' => 'Stefan Bayarri',
            'email' => 'stefan@webbundels.nl',
            'type_id' => 'Developer',
            'roles' => ['teacher'],
            'coins' => 200
        ],

        [
            'name' => 'Sjim de Munck',
            'email' => 'sjim@common-development.nl',
            'type_id' => 'Developer',
            'roles' => ['student'],
            'coins' => 99
        ],

        [
            'name' => 'Shaun de Munck',
            'email' => 'shaun@common-development.nl',
            'type_id' => 'Developer',
            'roles' => ['student'],
            'coins' => 74
        ],

        [
            'name' => 'Henny van der Veer',
            'email' => 'henny@pensioen-is-fijn.nl',
            'type_id' => 'Developer',
            'roles' => ['student', 'teacher'],
            'coins' => 77
        ],
    ];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = Type::get();
        foreach ($this->users as $user) {
            $namePieces = explode(' ', $user['name']);
            $user['password'] = substr($namePieces[0], 0, 3) . substr($namePieces[count($namePieces)-1], 0, 3) . '01';

            $user['type_id'] = $types->where('name', $user['type_id'])->first()->id;
            $userModel = User::create(Arr::except($user, ['roles']));

            $roleIds = Role::whereIn('name', $user['roles'])->pluck('id');
            $userModel->roles()->attach($roleIds->all());
        }
    }
}