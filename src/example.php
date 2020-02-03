<?php

namespace Jascha030\DIC;

use Jascha030\DIC\Container\Psr\PsrServiceContainer;

/**
 * Include psr-4 autoloader
 */
include "vendor/autoload.php";

/**
 * Class User
 *
 * Example dependency of UserService
 *
 * @package Jascha030\DIC
 */
class User
{
    public $name;

    /**
     * User constructor.
     *
     * @param string $name
     */
    public function __construct($name = "Jeff")
    {
        if (! empty($name)) {
            $this->name = $name;
        }
    }
}

/**
 * Class UserService
 *
 * Resolvable instance example
 *
 * @package Jascha030\DIC
 */
class UserService
{
    public $user;

    /**
     * UserService constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function printUserName()
    {
        echo "My name is " . $this->user->name;
    }
}

// Instantiate new Container
$container = new PsrServiceContainer();

$container->set(User::class, function () {
    return new User("Gerrit");
}, true);

/**
 * Get resolvable class
 *
 * In this case class is set by the get method.
 * You can also predefine a class with the set method or in the $instances argument of the constructor.
 */
$us = $container->get(UserService::class);

$us->printUserName(); //Outputs: My name is Jeff
