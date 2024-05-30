<?php

namespace Workbench\App\Models;

use Webbundels\Models\Model;

class Type extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
