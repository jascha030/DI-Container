<?php

namespace Jascha030\DIC\Psr;

use Exception;
use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Jascha030\DIC\Resolver\ResolverInterface;
use Psr\Container\ContainerInterface;

/**
 * Class PsrServiceContainer
 *
 * Simple PSR-11 compliant service container with dependency auto-wiring
 *
 * @package Jascha030\DIC\Psr
 * @author Jascha van Aalst
 * @since 1.0.0
 */
class PsrServiceContainer implements ContainerInterface, ResolverInterface
{
    /**
     * @var array[string] string|DefinitionInterface Instance definitions
     */
    protected $definitions = [];

    /**
     * @var array[string] Object Resolved instances
     */
    protected $resolvedInstances = [];

    /**
     * PsrServiceContainer constructor.
     *
     * @param array $definitions
     *
     * @throws Exception
     */
    public function __construct(array $definitions = [])
    {
        $definitions       = array_merge($this->definitions, $definitions);
        $this->definitions = [];

        foreach ($definitions as $className => $definition) {
            if (! is_string($className)) {
                $className = $definition;
            }

            $this->setDefinition($className, $definition);
        }
    }

    /**
     * Return all set definitions
     *
     * @return array
     * @since 1.1.0
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Set class or service
     *
     * @param $definitionName
     * @param DefinitionInterface $definition
     *
     * @throws Exception
     * @since 1.1.0
     */
    public function setDefinition($definitionName, DefinitionInterface $definition = null)
    {
        if ($this->isDefined($definitionName)) {
            return;
        }

        if (! $definition || is_string($definition)) {
            $definition = $this->getDefinitionType($definitionName);
        }

        $this->definitions[$definitionName] = $definition;
    }

    /**
     * Request class instance
     *
     * @param string $id
     *
     * @return mixed|object
     * @throws Exception
     */
    public function get($id)
    {
        if ($this->isResolved($id)) {
            return $this->resolvedInstances[$id];
        }

        return $this->resolve($id);
    }

    /**
     * Checks if resolved for PSR-11 compliance
     *
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->isResolved($id);
    }

    /**
     * Resolve requested definition
     *
     * @param $definitionName
     *
     * @return mixed
     * @throws Exception
     * @since 1.1.0
     */
    public function resolve($definitionName)
    {
        if (! $this->isDefined($definitionName)) {
            $this->setDefinition($definitionName);
        }

        $definition = $this->getDefinition($definitionName);

        $this->resolvedInstances[$definitionName] = $definition->resolve($this);

        return $this->resolvedInstances[$definitionName];
    }

    /**
     * Check if definition is already defined
     *
     * @param $definitionName
     *
     * @return bool
     * @since 1.1.0
     */
    protected function isDefined($definitionName)
    {
        return (isset($this->definitions[$definitionName]) || array_key_exists($definitionName,
                    $this->getDefinitions())) &&
               $this->definitions[$definitionName] instanceof DefinitionInterface;
    }

    /**
     * Check if requested definition is already resolved
     *
     * @param $id
     *
     * @return bool
     * @since 1.1.0
     */
    protected function isResolved($id)
    {
        return (isset($this->resolvedInstances[$id]) || array_key_exists($id, $this->resolvedInstances));
    }

    protected function getDefinitionType($definitionName): DefinitionInterface
    {
        if (class_exists($definitionName) || interface_exists($definitionName)) {
            return ObjectDefinition::define($definitionName);
        }

        throw new Exception(
            sprintf("No valid definition type was found for \"%s\"", $definitionName)
        );
    }

    private function getDefinition($definitionName): DefinitionInterface
    {
        if (! $this->isDefined($definitionName)) {
            throw new Exception(
                sprintf("No definition was found for \"%s\"", $definitionName)
            );
        }

        return $this->definitions[$definitionName];
    }
}
