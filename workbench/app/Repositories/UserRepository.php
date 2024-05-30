<?php

namespace Workbench\App\Repositories;

use Workbench\App\Models\User;

class UserRepository extends AbstractRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }
}