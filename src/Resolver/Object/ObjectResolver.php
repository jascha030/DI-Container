<?php

namespace Jascha030\DIC\Resolver\Object;

use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Jascha030\DIC\Exception\Dependency\UnresolvableDependencyException;
use Jascha030\DIC\Resolver\DefinitionResolverInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class ObjectResolver implements DefinitionResolverInterface
{
    private $definitionResolver;

    public function __construct(DefinitionResolverInterface $definitionResolver)
    {
        $this->definitionResolver = $definitionResolver;
    }

    public function resolve(DefinitionInterface $definition)
    {

    }

    /**
     * Resolve method dependencies for class constructor
     *
     * @param ReflectionMethod $method
     *
     * @return array
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

                $methodDependencies[] = ($dependencyDefinition instanceof ObjectDefinition)
                    ? $this->resolve($dependencyDefinition)
                    : $this->definitionResolver->resolve($dependencyDefinition);
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
