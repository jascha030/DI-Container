<?php

namespace Jascha030\DIC\Psr;

use Exception;
use Psr\Container\ContainerInterface;

class PsrServiceContainer implements ContainerInterface
{
    protected $instances = [];

    public function __construct($instances = [])
    {
        if (!empty($instances)) {
            foreach ($instances as $instance) {
                $this->set($instance);
            }
        }
    }

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
        return array_key_exists($id, $this->instances);
    }
}
