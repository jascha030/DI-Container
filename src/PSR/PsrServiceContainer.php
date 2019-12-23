<?php

namespace Jascha030\DIC\Psr;

use Psr\Container\ContainerInterface;

class PsrServiceContainer implements ContainerInterface
{
    protected $instances = [];

    public function get($id)
    {
        // TODO: Implement get() method.
    }

    public function has($id)
    {
        // TODO: Implement has() method.
    }
}
