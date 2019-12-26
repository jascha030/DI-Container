<?php

namespace Jascha030\DIC\Container\Psr;

use Closure;
use Exception;
use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Exception\Definition\DefinitionTypeNotFoundException;
use Jascha030\DIC\Resolver\Definition\DefinitionResolver;
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
class PsrServiceContainer implements ContainerInterface
{
    /**
     * @var array[string] Object
     */
    protected $entries = [];

    /**
     * @var array
     */
    protected $beingResolved = [];

    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * PsrServiceContainer constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->definitionResolver = new DefinitionResolver();
    }

    /**
     * Set definition
     *
     * @param string $name
     *
     * @param null $value
     *
     * @return bool|void
     * @since 1.4.0
     */
    public function set(string $name, $value = null)
    {
        if ($this->isResolved($name) && ! $value) {
            return;
        }

        if (! $value || $value instanceof DefinitionInterface) {
            try {
                $this->resolveDefinition($name, $value);
            } catch (DefinitionTypeNotFoundException $e) {
                return false;
            }
        }

        if ($value instanceof Closure) {
            $this->addEntry($name, $value);
        }

        return true;
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
        if (! $this->isResolved($id)) {
            $this->set($id);
        }

        if ($this->entries[$id] instanceof Closure) {
            return call_user_func($this->entries[$id], $this);
        }

        return $this->entries[$id];
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
     * @param $name
     *
     * @param DefinitionInterface|null $definition
     *
     * @throws DefinitionTypeNotFoundException
     * @since 1.3.0
     */
    private function resolveDefinition($name, DefinitionInterface $definition = null)
    {
        if (! $definition) {
            $definition = $this->definitionResolver->getDefinition($name);
        }

        if ($this->beingResolved($name)) {
            throw new Exception(
                sprintf("Circular dependency detected for entry \"%s\"", $name)
            );
        }

        $this->beingResolved[$name] = true;

        try {
            $entry = $this->definitionResolver->resolve($definition);
        } finally {
            unset($this->beingResolved[$name]);
        }

        $this->addEntry($name, $entry);
    }

    /**
     * Add instance to resolvedInstance
     *
     * @param string $abstract
     * @param mixed $concrete
     *
     * @since 1.3.0
     */
    private function addEntry($abstract, $concrete)
    {
        $this->entries[$abstract] = $concrete;
    }

    /**
     * Check if requested definition is already resolved
     *
     * @param $name
     *
     * @return bool
     *
     * @since 1.1.0
     */
    private function isResolved($name)
    {
        return (isset($this->entries[$name]) || array_key_exists($name, $this->entries));
    }

    /**
     * Check for circular dependency
     *
     * @param $name
     *
     * @return bool
     */
    private function beingResolved($name)
    {
        return (isset($this->beingResolved[$name]) || array_key_exists($name, $this->beingResolved));
    }
}
