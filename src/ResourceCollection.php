<?php

namespace Webbundels\Models;

use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;

class ResourceCollection extends LaravelResourceCollection
{
    public function __construct($resource)
    {
        $this->collects = str_replace('Collection', 'Resource', get_class($this));
        
        parent::__construct($resource);
    }
}
