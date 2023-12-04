<?php

namespace Webbundels\Models;

abstract class ModelService
{
    protected $repo;

    // Redirect every non existance function to the class attribute 'repo'.
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->repo, $name), $arguments);
    }
}
