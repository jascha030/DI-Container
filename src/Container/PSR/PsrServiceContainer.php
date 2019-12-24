<?php

namespace Jascha030\DIC\Container\Psr;

use Exception;
use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Jascha030\DIC\Resolver\DefinitionResolver;
use Psr\Container\ContainerInterface;
use Jascha030\DIC\Exception\Definition\DefinitionNotFoundException;
use Jascha030\DIC\Exception\Definition\DefinitionTypeNotFoundException;

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
     * @var array[string] mixed null|string|DefinitionInterface
     */
    protected $definitions = [];

    /**
     * @var array[string] DefinitionInterface
     */
    protected $defined = [];

    /**
     * @var array[string] Object
     */
    protected $resolvedInstances = [];

    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * PsrServiceContainer constructor.
     *
     * @param array $definitions
     *
     * @throws Exception
     */
    public function __construct(array $definitions = [])
    {
        $this->definitionResolver = new DefinitionResolver($this);
        $this->definitions        = array_merge($this->definitions, $definitions);

        foreach ($this->definitions as $className => $definition) {
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
    public function getDefined()
    {
        return $this->defined;
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

        $this->defined[$definitionName] = $definition;
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

        return $this->resolveDefinition($id);
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
    public function resolveDefinition($definitionName)
    {
        if (! $this->isDefined($definitionName)) {
            $this->setDefinition($definitionName);
        }
        $definition = $this->getDefinition($definitionName);
        return $this->addInstance($definitionName, $this->definitionResolver->resolve($definition));
    }

    protected function addInstance($definition, $instance)
    {
        if (!$this->isResolved($definition))
        $this->resolvedInstances[$definition] = $instance;

        return $this->resolvedInstances[$definition];
    }

    /**
     * Check if definition is already defined
     *
     * @param $definitionName
     *
     * @return bool
     * @since 1.1.0
     */
    private function isDefined($definitionName)
    {
        return (isset($this->defined[$definitionName]) || array_key_exists($definitionName, $this->getDefined()))
               && $this->defined[$definitionName] instanceof DefinitionInterface;
    }

    /**
     * Check if requested definition is already resolved
     *
     * @param $id
     *
     * @return bool
     * @since 1.1.0
     */
    private function isResolved($id)
    {
        return (isset($this->resolvedInstances[$id]) || array_key_exists($id, $this->resolvedInstances));
    }

    private function getDefinitionType($definitionName): DefinitionInterface
    {
        if (class_exists($definitionName) || interface_exists($definitionName)) {
            return ObjectDefinition::define($definitionName);
        }

        throw new DefinitionTypeNotFoundException(
            sprintf("No valid definition type was found for \"%s\"", $definitionName)
        );
    }

    private function getDefinition($definitionName): DefinitionInterface
    {
        if (! $this->isDefined($definitionName)) {
            throw new DefinitionNotFoundException(
                sprintf("No definition was found for \"%s\"", $definitionName)
            );
        }

        return $this->defined[$definitionName];
    }
}
