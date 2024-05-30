<?php

use Workbench\App\Models\User;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RelationFilterTest extends \Orchestra\Testbench\TestCase
{

    use RefreshDatabase;
    use WithWorkbench;
    
    /** @test */
    public function test_relation_filter()
    {
        $users = User::filter(['roles' => ['name_is' => 'baba-voss']])->get();
        $this->assertTrue($users->count() === 1);
        $this->assertTrue($users->first()->name === 'Nick van Leeuwen');
    }
    
    /** @test */
    public function test_relation_many_to_many_with()
    {
        $users = User::withs(['roles' => ['name_is' => 'baba-voss']])->get();

        $this->assertTrue($users->count() === 6);
        $this->assertTrue($users->filter(function($user) {
            return $user->getRelations()['roles']->count() === 1;
        })->count() === 1);
        $this->assertTrue($users->filter(function($user) {
            return $user->getRelations()['roles']->count() === 0;
        })->count() === 5);
    }
    
    /** @test */
    public function test_where_doesnt_have()
    {
        $users = User::filter(['!roles' => ['name_is' => 'baba-voss']])->pluck('name')->all();

        $this->assertEquals($users, ['Stephan Baggerman', 'Stefan Bayarri', 'Sjim de Munck', 'Shaun de Munck', 'Henny van der Veer']);
    }
    
    /** @test */
    public function test_relation_many_to_one_with()
    {
        $users = User::withs(['type' => ['name_is' => 'Developer']])->get();

        $this->assertTrue($users->count() === 6);
        $this->assertTrue($users->filter(function($user) {
            return (
                array_key_exists('type', $user->getRelations()) 
                && $user->getRelations()['type']
                && $user->getRelations()['type']->name === 'Developer'
            );
        })->count() === 5);
        $this->assertTrue($users->filter(function($user) {
            return (
                array_key_exists('type', $user->getRelations()) 
                && $user->getRelations()['type'] === null
            );
        })->count() === 1);

        
    }
}