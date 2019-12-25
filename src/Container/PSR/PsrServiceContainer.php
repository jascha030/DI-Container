<?php

namespace Jascha030\DIC\Container\Psr;

use Closure;
use Exception;
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
     * @param $name
     *
     * @return bool
     *
     * @throws DefinitionTypeNotFoundException
     * @since 1.4.0
     */
    public function set($name)
    {
        try {
            $this->definitionResolver->setDefinition($name);
        } catch (DefinitionTypeNotFoundException $e) {
            return false;
        }

        $this->resolve($name);

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
            return call_user_func($this->entries[$id]);
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
     * @param $id
     *
     * @throws DefinitionTypeNotFoundException
     *
     * @since 1.3.0
     */
    private function resolve($id)
    {
        if (! $this->isResolved($id)) {
            $definition = $this->definitionResolver->getDefinition($id);

            $this->add($id, $this->definitionResolver->resolve($definition));
        }
    }

    /**
     * Add instance to resolvedInstance
     *
     * @param $abstract
     * @param Closure $closure
     *
     * @since 1.3.0
     */
    private function add($abstract, $closure)
    {
        $this->entries[$abstract] = $closure;
    }

    /**
     * Check if requested definition is already resolved
     *
     * @param $id
     *
     * @return bool
     *
     * @since 1.1.0
     */
    private function isResolved($id)
    {
        return (isset($this->entries[$id]) || array_key_exists($id, $this->entries));
    }
}
