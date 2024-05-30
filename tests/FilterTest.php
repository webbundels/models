<?php

use Workbench\App\Models\Role;
use Workbench\App\Models\User;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterTest extends \Orchestra\Testbench\TestCase
{

    use RefreshDatabase;
    use WithWorkbench;
    
    /** @test */
    public function test_is()
    {
        $users = User::filter(['name_is' => 'Nick van Leeuwen'])->get();

        $this->assertTrue($users->count() === 1);
        $this->assertTrue($users->first()->name === 'Nick van Leeuwen');
    }
    
    /** @test */
    public function test_is_greater()
    {
        $users = User::filter(['coins_is_greater_than' => 100])->pluck('name')->all();

        $this->assertTrue(count($users) === 1);
        $this->assertEquals($users, ['Stefan Bayarri']);
    }
    
    /** @test */
    public function test_is_greater_or_equal_than()
    {
        $users = User::filter(['coins_is_greater_or_equal_than' => 100])->pluck('name')->all();

        $this->assertTrue(count($users) === 2);
        $this->assertEquals($users, ['Nick van Leeuwen', 'Stefan Bayarri']);
    }
    
    /** @test */
    public function test_is_less_than()
    {
        $users = User::filter(['coins_is_lower_than' => 100])->pluck('name')->all();

        $this->assertTrue(count($users) === 4);
        $this->assertEquals($users, ['Stephan Baggerman', 'Sjim de Munck', 'Shaun de Munck', 'Henny van der Veer']);
    }
    
    /** @test */
    public function test_is_lower_or_equal_than()
    {
        $users = User::filter(['coins_is_lower_or_equal_than' => 100])->pluck('name')->all();

        $this->assertTrue(count($users) === 5);
        $this->assertEquals($users, ['Nick van Leeuwen', 'Stephan Baggerman', 'Sjim de Munck', 'Shaun de Munck', 'Henny van der Veer']);
    }

    public function test_is_in()
    {
        $users = User::filter(['coins_is_in' => [99,100, 101, 102, 103]])->pluck('name')->all();

        $this->assertTrue(count($users) === 2);
        $this->assertEquals($users, ['Nick van Leeuwen', 'Sjim de Munck']);
    }

    public function test_take()
    {
        $users = User::filter(['take' => 2])->get();

        $this->assertTrue(count($users) === 2);
    }
    
    public function test_skip()
    {
        $users = User::filter(['take' => 2, 'skip' => 2])->pluck('name')->all();

        $this->assertEquals($users, ['Stefan Bayarri', 'Sjim de Munck']);
    }

    public function test_soft_deletes()
    {
        $users = User::filter(['with_trashed'])->get();
        
        $this->assertTrue(count($users) === 6);

        $users = User::filter(['only_trashed'])->get();

        $this->assertTrue(count($users) === 0);

        $user = User::where('id', 1)->delete();

        $users = User::filter(['with_trashed'])->get();
        
        $this->assertTrue(count($users) === 6);

        $users = User::filter(['only_trashed'])->get();

        $this->assertTrue(count($users) === 1);
    }

    public function test_without_scope()
    {
        $roles = Role::pluck('name')->all();
        
        $this->assertEquals($roles, ['admin', 'baba-voss', 'student', 'teacher']);

        $roles = Role::filter(['without_scope' => 'default_order'])->pluck('name')->all();

        $this->assertEquals($roles, ['baba-voss', 'admin', 'teacher', 'student']);  

        $roles = Role::filter(['without_scopes'])->pluck('name')->all();
    }

    public function test_order_by()
    {
        $users = User::filter(['order_by' => 'name'])->pluck('name')->all();

        $this->assertEquals($users, ['Henny van der Veer', 'Nick van Leeuwen', 'Shaun de Munck', 'Sjim de Munck', 'Stefan Bayarri', 'Stephan Baggerman']);

        $users = User::filter(['sort' => 'name'])->pluck('name')->all();

        $this->assertEquals($users, ['Henny van der Veer', 'Nick van Leeuwen', 'Shaun de Munck', 'Sjim de Munck', 'Stefan Bayarri', 'Stephan Baggerman']);

        $users = User::filter(['order_by_desc' => 'name'])->pluck('name')->all();

        $this->assertEquals($users, ['Stephan Baggerman', 'Stefan Bayarri', 'Sjim de Munck', 'Shaun de Munck', 'Nick van Leeuwen', 'Henny van der Veer']);

        $users = User::filter(['sort' => '-name'])->pluck('name')->all();

        $this->assertEquals($users, ['Stephan Baggerman', 'Stefan Bayarri', 'Sjim de Munck', 'Shaun de Munck', 'Nick van Leeuwen', 'Henny van der Veer']);
    }
}