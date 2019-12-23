<?php

namespace Jascha030\DIC\Psr;

use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

class PsrServiceContainer implements ContainerInterface
{
    protected $instances = [];

    public function __construct($instances = [])
    {
        if ( ! empty($instances)) {
            foreach ($instances as $instance) {
                $this->set($instance);
            }
        }
    }

    public function set($className, $concrete = null)
    {
        if ( ! class_exists($className)) {
            throw new Exception("Class {$className} could not be found");
        }

        if ( ! $concrete) {
            $concrete = $className;
        }

        $this->instances[$className] = $concrete;
    }

    public function get($id)
    {
        if ( ! $this->has($id)) {
            $this->set($id);
        }

        return $this->resolve($this->instances[$id]);
    }

    public function has($id)
    {
        return array_key_exists($id, $this->instances);
    }

    public function resolve($className)
    {
        $reflected = new ReflectionClass($className);

        if ( ! $reflected->isInstantiable()) {
            throw new Exception("Class {$className} is not instantiable");
        }

        $reflectedConstructor = $reflected->getConstructor();

        if ( ! $reflectedConstructor) {
            return $reflected->newInstance();
        }

        $constructorParameters = $reflectedConstructor->getParameters();
        $constructorDependencies = $this->getDependencies($constructorParameters);

        return $reflected->newInstanceArgs($constructorDependencies);
    }

    public function getDependencies($parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            /** @var ReflectionParameter $parameter */
            $dependency = $parameter->getClass();

            if ( ! $dependency) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("Can't resolve dependency {$parameter->name}");
                }
            } else {
                $dependencies[] = $this->get($dependency->name);
            }
        }

        return $dependencies;
    }
}
