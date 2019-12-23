<?php

namespace Jascha030\DIC\Psr;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Jascha030\DIC\Exception\ClassNotFoundException;
use Jascha030\DIC\Exception\ClassNotInstantiableException;
use Jascha030\DIC\Exception\Dependency\UnresolvableDependencyException;

/**
 * Class PsrServiceContainer
 *
 * Simple PSR-11 compliant service container with dependency auto-wiring
 *
 * @package Jascha030\DIC\Psr
 * @author Jascha van Aalst
 * @since 1.0.0
 */
class PsrServiceContainer implements ContainerInterface
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * PsrServiceContainer constructor.
     *
     * @param array $instances
     *
     * @throws ClassNotFoundException
     */
    public function __construct($instances = [])
    {
        if ( ! empty($instances)) {
            foreach ($instances as $instance) {
                $this->set($instance);
            }
        }
    }

    /**
     * Set class or service
     *
     * @param $className
     * @param null $concrete
     *
     * @throws ClassNotFoundException
     */
    public function set($className, $concrete = null)
    {
        if ( ! class_exists($className)) {
            throw new ClassNotFoundException("Class {$className} could not be found");
        }

        if ( ! $concrete) {
            $concrete = $className;
        }

        $this->instances[$className] = $concrete;
    }

    /**
     * Request class instance
     *
     * @param string $id
     *
     * @return mixed|object
     * @throws ClassNotFoundException
     * @throws ClassNotInstantiableException
     * @throws ReflectionException
     * @throws UnresolvableDependencyException
     */
    public function get($id)
    {
        if ( ! $this->has($id)) {
            $this->set($id);
        }

        return $this->resolve($this->instances[$id]);
    }

    /**
     * Check if class instance already present in instances array
     *
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Resolve new class instance
     *
     * @param $className
     *
     * @return object
     * @throws ClassNotInstantiableException
     * @throws UnresolvableDependencyException
     * @throws ReflectionException
     * @throws ClassNotFoundException
     */
    public function resolve($className)
    {
        $reflected = new ReflectionClass($className);

        if ( ! $reflected->isInstantiable()) {
            throw new ClassNotInstantiableException("Class {$className} is not instantiable");
        }

        $reflectedConstructor = $reflected->getConstructor();

        if ( ! $reflectedConstructor) {
            return $reflected->newInstance();
        }

        $constructorParameters   = $reflectedConstructor->getParameters();
        $constructorDependencies = $this->getDependencies($constructorParameters);

        return $reflected->newInstanceArgs($constructorDependencies);
    }

    /**
     * Get class instance dependencies
     *
     * @param $parameters
     *
     * @return array
     * @throws ClassNotFoundException
     * @throws ClassNotInstantiableException
     * @throws UnresolvableDependencyException
     * @throws ReflectionException
     */
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
                    throw new UnresolvableDependencyException("Can't resolve dependency {$parameter->name}");
                }
            } else {
                $dependencies[] = $this->get($dependency->name);
            }
        }

        return $dependencies;
    }
}
