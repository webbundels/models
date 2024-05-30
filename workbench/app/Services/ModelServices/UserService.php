<?php

namespace Workbench\App\Services\ModelServices;

use Workbench\App\Repositories\UserRepository;
use Workbench\App\Services\ModelServices\AbstractModelService;

class UserService extends AbstractModelService
{
    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }
}
