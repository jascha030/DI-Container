<?php

namespace Jascha030\DIC\Psr;

use Exception;
use Psr\Container\ContainerInterface;

class PsrServiceContainer implements ContainerInterface
{
    protected $instances = [];

    public function set($abstract, $concrete = null)
    {
        if (!class_exists($abstract)) {
            throw new Exception("Class {$abstract} could not be found");
        }

        if (!$concrete) {
            $concrete = $abstract;
        }

        $this->instances[$abstract] = $concrete;
    }

    public function get($id)
    {
        // TODO: Implement get() method.
    }

    public function has($id)
    {
        // TODO: Implement has() method.
    }
}
