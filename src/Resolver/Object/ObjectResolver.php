<?php

namespace Jascha030\DIC\Resolver\Object;

use Closure;
use Jascha030\DIC\Container\Psr\PsrServiceContainer;
use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Jascha030\DIC\Exception\ClassNotFoundException;
use Jascha030\DIC\Exception\ClassNotInstantiableException;
use Jascha030\DIC\Exception\Dependency\UnresolvableDependencyException;
use Jascha030\DIC\Resolver\DefinitionResolverInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class ObjectResolver
 *
 * @package Jascha030\DIC\Resolver\Object
 * @author Jascha van Aalst
 * @since 1.5.0
 */
class ObjectResolver implements DefinitionResolverInterface
{
    /**
     * @var DefinitionResolverInterface
     */
    private $definitionResolver;

    /**
     * ObjectResolver constructor.
     *
     * @param DefinitionResolverInterface $definitionResolver
     */
    public function __construct(DefinitionResolverInterface $definitionResolver)
    {
        $this->definitionResolver = $definitionResolver;
    }

    /**
     * @param $dependency
     *
     * @return mixed
     */
    public static function isClosure(&$dependency)
    {
        if ($dependency instanceof Closure) {
            $dependency = call_user_func($dependency);
        }

        return $dependency;
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @return Closure|mixed
     * @throws ClassNotFoundException
     * @throws ClassNotInstantiableException
     * @throws ReflectionException
     * @throws UnresolvableDependencyException
     */
    public function resolve(DefinitionInterface $definition)
    {
        $definitionName = $definition->getName();

        if (! class_exists($definitionName)) {
            throw new ClassNotFoundException(sprintf("Class \"%s\" cannot be found", $definitionName));
        }

        try {
            $reflectionMethod = new ReflectionMethod($definitionName, "__construct");
        } catch (ReflectionException $e) {

            $reflectionClass = new ReflectionClass($definitionName);

            if ($reflectionClass->isInstantiable()) {
                return function () use ($definitionName) {
                    return new $definitionName();
                };
            } else {
                throw new ClassNotInstantiableException(sprintf("Class \"%s\" is not instantiable",
                        $definition->getName()));
            }
        }

        $methodArguments = $this->resolveMethodDependencies($reflectionMethod);

        return function () use ($definitionName, $methodArguments) {
            array_walk($methodArguments, [ObjectResolver::class, 'isClosure']);

            return new $definitionName(...$methodArguments);
        };
    }

    public function hasContainer()
    {
        return $this->definitionResolver->hasContainer();
    }

    private function getContainer(): PsrServiceContainer
    {
        return $this->definitionResolver->container;
    }

    /**
     * @param ReflectionMethod $method
     *
     * @return array
     * @throws ClassNotFoundException
     * @throws ClassNotInstantiableException
     * @throws ReflectionException
     * @throws UnresolvableDependencyException
     */
    protected function resolveMethodDependencies(ReflectionMethod $method)
    {
        $methodDependencies = [];

        foreach ($method->getParameters() as $parameter) {
            /** @var ReflectionParameter $parameter */
            $dependency = $parameter->getClass();

            if ($dependency) {
                $dependencyDefinition = $this->definitionResolver->getDefinition($dependency->getName());

                if ($this->hasContainer() && $this->getContainer()->isShared($dependencyDefinition->getName())) {
                    $methodDependencies[] = $this->getContainer()->get($dependencyDefinition->getName());
                } else {
                    $methodDependencies[] = ($dependencyDefinition instanceof ObjectDefinition) ? $this->resolve($dependencyDefinition) : $this->definitionResolver->resolve($dependencyDefinition);
                }
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $methodDependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new UnresolvableDependencyException(sprintf("Can't resolve dependency \"%s\"",
                            $parameter->name));
                }
            }
        }

        return $methodDependencies;
    }
}
