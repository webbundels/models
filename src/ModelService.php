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

    // TODO: add paginate method to repository.php
    public function paginate($input, $with = [])
    {
        $input['per_page'] = array_key_exists('per_page', $input) ? $input['per_page'] : 10;

        return $this->repo->paginate($input, $with);
    }
}
