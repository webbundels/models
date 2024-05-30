<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Webbundels\Models\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password', 'type_id', 'coins'];

    public $timestamps = true;

    use SoftDeletes;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }


}