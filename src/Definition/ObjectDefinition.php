<?php

namespace Jascha030\DIC\Definition;

use Jascha030\DIC\Exception\ClassNotFoundException;
use Jascha030\DIC\Exception\Dependency\UnresolvableDependencyException;
use Jascha030\DIC\Resolver\ResolverInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class ObjectDefinition implements DefinitionInterface
{
    protected $definitionName;

    public function __construct($className)
    {
        $this->definitionName = $className;
    }

    public static function define($className)
    {
        return new ObjectDefinition($className);
    }

    public function resolve(ResolverInterface $resolver)
    {
        if ( ! class_exists($this->definitionName)) {
            throw new ClassNotFoundException(
                sprintf("Class \"%s\" cannot be found", $this->definitionName)
            );
        }

        try {
            $reflectionMethod = new ReflectionMethod($this->definitionName, "__construct");
        } catch (ReflectionException $e) {
            return new $this->definitionName();
        }

        $methodArguments = $this->resolveMethodDependencies($reflectionMethod, $resolver);

        return new $this->definitionName(...$methodArguments);
    }

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
