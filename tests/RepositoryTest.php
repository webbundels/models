<?php

use Workbench\App\Models\Role;
use Workbench\App\Models\Type;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\App;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepositoryTest extends \Orchestra\Testbench\TestCase
{

    use RefreshDatabase;
    use WithWorkbench;
    
    /** @test */
    public function test_get()
    {
        $users = App::make('UserService')->get(['name_is' => 'Nick van Leeuwen']);

        $this->assertTrue($users->count() === 1);
        $this->assertTrue($users->first()->name === 'Nick van Leeuwen');
    }

    /** @test */
    public function test_first()
    {
        $users = App::make('UserService')->first(['name_not_is' => 'Nick van Leeuwen']);

        $this->assertTrue($users->first()->name !== 'Stephan Baggerman');
    }

    /** @test */
    public function test_get_all()
    {
        $users = App::make('UserService')->getAll();

        $this->assertTrue($users->count() === 6);
    }

    /** @test */
    public function test_get_by_id()
    {
        $user = App::make('UserService')->getById(2);

        $this->assertTrue($user->id === 2);
    }

    /** @test */
    public function test_get_by_ids()
    {
        $users = App::make('UserService')->getByIds([2, 4]);

        $this->assertEquals($users->pluck('name')->all(), ['Stephan Baggerman', 'Sjim de Munck']);
    }

    /** @test */
    public function test_get_by_name()
    {
        $user = App::make('UserService')->getByName('Shaun Baggerman');
        $this->assertTrue($user === null);

        $user = App::make('UserService')->getByName('Stephan Baggerman');
        $this->assertTrue($user->name === 'Stephan Baggerman');
    }

    /** @test */
    public function test_pluck_ids()
    {
        $userIds = App::make('UserService')->pluckIds(['coins_is_in' => [50,200]])->all();

        $this->assertEquals($userIds, [2, 3]);
    }

    /** @test */
    public function test_pluck()
    {
        $userCoins = App::make('UserService')->pluck('coins', ['id_is_in' => [1,4]])->all();

        $this->assertEquals($userCoins, [100, 99]);
    }

    /** @test */
    public function test_pluck_with_keys()
    {
        $userCoins = App::make('UserService')->pluckWithKeys('name', 'coins', ['id_is_in' => [1,4]])->all();

        $this->assertEquals($userCoins, [100 => 'Nick van Leeuwen', 99 => 'Sjim de Munck']);
    }

    /** @test */
    public function test_count()
    {
        $count = App::make('UserService')->count(['id_is_in' => [1,4]]);

        $this->assertTrue($count === 2);
    }

    /** @test */
    public function test_paginate()
    {
        
        $users = App::make('UserService')->paginate(['per_page' => 2]);
        
        $this->assertEquals(array_map(function($u){return $u->email;}, $users->items()), ['nick@webbundels.nl', 'stephan@webbundels.nl']);
        $this->assertTrue($users->currentPage() === 1);
        $this->assertTrue($users->count() === 2);
    }

    /** @test */
    public function test_increment()
    {
        $oldCoins = App::make('UserService')->getById(1)->coins;
        App::make('UserService')->increment('coins', ['id_is' => 1], 2);
        $coins = App::make('UserService')->getById(1)->coins;

        $this->assertTrue($coins === $oldCoins+2);
    }

    /** @test */
    public function test_decrement()
    {
        $oldCoins = App::make('UserService')->getById(1)->coins;
        App::make('UserService')->decrement('coins', ['id_is' => 1], 2);
        $coins = App::make('UserService')->getById(1)->coins;

        $this->assertTrue($coins === $oldCoins-2);
    }

    /** @test */
    public function test_store()
    {
        $user = App::make('UserService')->store([
            'name' => 'John Doe',
            'email' => 'john@doe.nl',
            'type_id' => Type::first()->id,
            'coins' => 77,
            'password' => '1234'
        ]);

        $this->assertTrue($user->name === 'John Doe');

        $user = App::make('UserService')->getByName('John Doe');
        $this->assertTrue($user !== null);

    }

    /** @test */
    public function test_store_many()
    {
        App::make('UserService')->storeMany([
            [
                'name' => 'Peter Poffertjes',
                'email' => 'peter@poffertje.nl',
                'type_id' => Type::first()->id,
                'coins' => 77,
                'password' => '1234'
            ], [
                'name' => 'Memphis de Paai',
                'email' => 'memphis@paai.nl',
                'type_id' => Type::first()->id,
                'coins' => 104,
                'password' => '1234'
            ]
        ]);

        $userNames = App::make('UserService')->get(['name_is_in' => ['Peter Poffertjes', 'Memphis de Paai']])->pluck('name')->all();
        
        $this->assertEquals($userNames, ['Peter Poffertjes', 'Memphis de Paai']);
    }

    /** @test */
    public function test_first_or_store()
    {
        $count = App::make('UserService')->count();

        App::make('UserService')->firstOrStore([
            'name' => 'Bert van der Ernie',
            'email' => 'bert@ernie.nl',
            'type_id' => Type::first()->id,
            'coins' => 77,
            'password' => '1234'
        ]);
        $newCount = App::make('UserService')->count();

        $this->assertTrue($newCount === $count+1);

        App::make('UserService')->firstOrStore([
            'name' => 'Bert van der Ernie',
            'email' => 'bert@ernie.nl',
            'type_id' => Type::first()->id,
            'coins' => 77,
            'password' => '1234'
        ]);
        $newCount = App::make('UserService')->count();

        $this->assertTrue($newCount === $count+1);
    }

    /** @test */
    public function test_update_or_create()
    {
        App::make('UserService')->updateOrCreate([
            'name' => 'Zac ff On',
            'email' => 'zac@on.nl',
            'type_id' => Type::first()->id,
            'password' => '1234',
        ], [
                'coins' => 88
        ]);

        $user = App::make('UserService')->getByName('Zac ff On');

        $this->assertTrue($user !== null);
        $this->assertTrue($user->coins === 88);

        App::make('UserService')->updateOrCreate([
            'name' => 'Zac ff On',
            'email' => 'zac@on.nl',
            'type_id' => Type::first()->id,
            'password' => '1234',
        ], [
            'coins' => 99
        ]);

        $user = App::make('UserService')->getByName('Zac ff On');

        $this->assertTrue($user->coins === 99);
    }

    /** @test */
    public function test_update()
    {
        $user = App::make('UserService')->store([
            'name' => 'Eric klaptaan',
            'email' => 'Eric@klaptaan.nl',
            'type_id' => Type::first()->id,
            'coins' => 11,
            'password' => '1234'
        ]);
        $user = App::make('UserService')->update($user->id, ['coins' => 30]);

        $this->assertTrue($user->coins === 30);
    }

    /** @test */
    public function test_update_filtered()
    {
        $user = App::make('UserService')->store([
            'name' => 'Walt Dist Niet',
            'email' => 'walt@dist.nl',
            'type_id' => Type::first()->id,
            'coins' => 142,
            'password' => '1234'
        ]);

        App::make('UserService')->updateFiltered(['name_is' => 'Walt Dist Niet'], ['coins' => 88]);

        $user = App::make('UserService')->getByName('Walt Dist Niet');
        $this->assertTrue($user->coins === 88);
    }

    /** @test */
    public function test_destroy()
    {
        $user = App::make('UserService')->store([
            'name' => 'Ozzy Oz Born',
            'email' => 'ozzy@born.nl',
            'type_id' => Type::first()->id,
            'coins' => 142,
            'password' => '1234'
        ]);

        $user = App::make('UserService')->getByName('Ozzy Oz Born');
        App::make('UserService')->destroy($user->id);

        $user = App::make('UserService')->getByName('Ozzy Oz Born');
        $this->assertTrue($user === null);
    }

    /** @test */
    public function test_destroy_many()
    {
        $users = App::make('UserService')->get(['name_is_in' => ['Stephan Baggerman', 'Sjim de Munck']]);

        $this->assertTrue($users->count() === 2);

        App::make('UserService')->destroyMany($users->pluck('id')->all());

        $users = App::make('UserService')->get(['name_is_in' => ['Stephan Baggerman', 'Sjim de Munck']]);
        $this->assertTrue($users->count() === 0);

        $users = App::make('UserService')->get(['name_is_in' => ['Stephan Baggerman', 'Sjim de Munck'], 'with_trashed']);
        $this->assertTrue($users->count() === 2);
    }

    /** @test */
    public function test_force_destroy()
    {
        $user = App::make('UserService')->getById(1);

        $this->assertTrue($user !== null);

        $user = App::make('UserService')->forceDestroy(1);

        $user = App::make('UserService')->first(['id_is' => 1, 'with_trashed']);

        $this->assertTrue($user === null);
    }

}