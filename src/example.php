<?php

namespace Jascha030\DIC;

use Jascha030\DIC\Psr\PsrServiceContainer;

include "vendor/autoload.php";

class User {
    public $name = "Jeff";

    public function __construct($name = "")
    {
        if (!empty($name)) {
            $this->name = $name;
        }
    }
}

class UserService {
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function printUserName()
    {
        echo "My name is " . $this->user->name;
    }
}

$container = new PsrServiceContainer();

$us = $container->get(UserService::class);

$us->printUserName(); //Outputs: My name is Jeff
