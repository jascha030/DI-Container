<?php

namespace Jascha030\DIC\Definition;

use Jascha030\DIC\Exception\ClassNotFoundException;
use Jascha030\DIC\Exception\ClassNotInstantiableException;
use Jascha030\DIC\Exception\Dependency\UnresolvableDependencyException;
use Jascha030\DIC\Resolver\ResolverInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class ObjectDefinition
 *
 * @package Jascha030\DIC\Definition
 * @author Jascha van Aalst
 * @since 1.1.0
 */
class ObjectDefinition implements DefinitionInterface
{
    /**
     * @var string $definitionName
     */
    protected $definitionName;

    /**
     * ObjectDefinition constructor.
     *
     * @param $className
     */
    public function __construct($className)
    {
        $this->definitionName = $className;
    }

    /**
     * Set definition
     *
     * @param $className
     *
     * @return ObjectDefinition
     */
    public static function define($className)
    {
        return new ObjectDefinition($className);
    }

    /**
     * Resolve object definition
     *
     * @param ResolverInterface $resolver
     *
     * @return mixed
     * @throws ClassNotFoundException
     * @throws UnresolvableDependencyException
     * @throws ReflectionException
     * @throws ClassNotInstantiableException
     */
    public function resolve(ResolverInterface $resolver)
    {
        if (! class_exists($this->definitionName)) {
            throw new ClassNotFoundException(
                sprintf("Class \"%s\" cannot be found", $this->definitionName)
            );
        }

        try {
            $reflectionMethod = new ReflectionMethod($this->definitionName, "__construct");
        } catch (ReflectionException $e) {
            $reflectionClass = new ReflectionClass($this->definitionName);
            if ($reflectionClass->isInstantiable()) {
                return new $this->definitionName();
            } else {
                throw new ClassNotInstantiableException(
                    sprintf("Class \"%s\" is not instantiable", $this->definitionName)
                );
            }
        }

        $methodArguments = $this->resolveMethodDependencies($reflectionMethod, $resolver);

        return new $this->definitionName(...$methodArguments);
    }

    /**
     * Resolve method dependencies for class constructor
     *
     * @param ReflectionMethod $method
     * @param ResolverInterface $resolver
     *
     * @return array
     * @throws ReflectionException
     * @throws UnresolvableDependencyException
     */
    protected function resolveMethodDependencies(ReflectionMethod $method, ResolverInterface $resolver)
    {
        $methodDependencies = [];

        foreach ($method->getParameters() as $parameter) {
            /** @var ReflectionParameter $parameter */
            $dependency = $parameter->getClass();

            if ($dependency) {
                $methodDependencies[] = $resolver->resolve($dependency->getName());
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $methodDependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new UnresolvableDependencyException(
                        sprintf("Can't resolve dependency \"%s\"", $parameter->name)
                    );
                }
            }
        }

        return $methodDependencies;
    }
}
