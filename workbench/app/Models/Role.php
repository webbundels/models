<?php

namespace Workbench\App\Models;

use Webbundels\Models\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Role extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('default_order', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }
}
