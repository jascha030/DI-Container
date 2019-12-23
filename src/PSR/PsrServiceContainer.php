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
     * @var array Resolved instances
     */
    protected $resolvedDefinitions = [];

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
            if (!is_string($className)) {
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
     * @since 1.1.0
     */
    public function setDefinition($definitionName, DefinitionInterface $definition = null)
    {
        if ( ! $definition || is_string($definition)) {
            $definition = ObjectDefinition::define($definitionName);
        }

        $this->definitions[$definitionName] = $definition;
    }

    /**
     * Request class instance
     *
     * @param string $id
     *
     * @return mixed|object
     */
    public function get($id)
    {
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
     * @since 1.1.0
     */
    public function resolve($definitionName)
    {
        if ($this->isResolved($definitionName)) {
            return $this->resolvedDefinitions[$definitionName];
        }

        if ( ! $this->isDefined($definitionName)) {
            $this->setDefinition($definitionName);
        }

        $definition = $this->definitions[$definitionName];

        $this->resolvedDefinitions[$definitionName] = $definition->resolve($this);

        return $this->resolvedDefinitions[$definitionName];
    }

    /**
     * Check if definition is already defined
     *
     * @param $className
     *
     * @return bool
     * @since 1.1.0
     */
    protected function isDefined($className)
    {
        return array_key_exists($className, $this->getDefinitions());
    }

    /**
     * Check if requested definition is already resolved
     *
     * @param $definition
     *
     * @return bool
     * @since 1.1.0
     */
    protected function isResolved($definition)
    {
        return array_key_exists($definition, $this->resolvedDefinitions);
    }
}
