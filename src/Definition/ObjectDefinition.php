<?php

namespace Jascha030\DIC\Definition;

use Jascha030\DIC\Exception\ClassNotFoundException;
use Jascha030\DIC\Exception\ClassNotInstantiableException;
use Jascha030\DIC\Exception\Dependency\UnresolvableDependencyException;
use Jascha030\DIC\Resolver\DefinitionResolverInterface;
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
     * @param DefinitionResolverInterface $resolver
     *
     * @return mixed
     * @throws ClassNotFoundException
     * @throws UnresolvableDependencyException
     * @throws ReflectionException
     * @throws ClassNotInstantiableException
     */
    public function resolve(DefinitionResolverInterface $resolver): \Closure
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
                return function () {
                    return new $this->definitionName();
                };
            } else {
                throw new ClassNotInstantiableException(
                    sprintf("Class \"%s\" is not instantiable", $this->definitionName)
                );
            }
        }

        $methodArguments = $this->resolveMethodDependencies($reflectionMethod, $resolver);

        return function () use ($methodArguments) {
            array_walk($methodArguments, [$this, 'isClosure']);

            return new $this->definitionName(...$methodArguments);
        };
    }

    public function isClosure(&$dependency, $index)
    {
        if ($dependency instanceof \Closure) {
            $dependency = call_user_func($dependency);
        }

        return $dependency;
    }

    /**
     * Resolve method dependencies for class constructor
     *
     * @param ReflectionMethod $method
     * @param DefinitionResolverInterface $resolver
     *
     * @return array
     * @throws ReflectionException
     * @throws UnresolvableDependencyException
     */
    protected function resolveMethodDependencies(ReflectionMethod $method, DefinitionResolverInterface $resolver)
    {
        $methodDependencies = [];

        foreach ($method->getParameters() as $parameter) {
            /** @var ReflectionParameter $parameter */
            $dependency = $parameter->getClass();
            if ($dependency) {
                $dependencyDefinition = $resolver->getDefinition($dependency->getName());
                $methodDependencies[] = $resolver->resolve($dependencyDefinition);
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
