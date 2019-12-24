<?php

namespace Jascha030\DIC\Container\Psr;

use Exception;
use Jascha030\DIC\Exception\Definition\DefinitionNotFoundException;
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
    protected $instances = [];

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
        $this->definitionResolver = new DefinitionResolver($definitions);
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
        return $this->resolveInstance($id);
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
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws DefinitionTypeNotFoundException
     *
     * @since 1.3.0
     */
    private function resolveInstance($id)
    {
        if ($this->isResolved($id)) {
            return $this->instances[$id];
        }

        return $this->addInstance($id, $this->definitionResolver->resolve($id));
    }

    /**
     * Add instance to resolvedInstance
     *
     * @param $abstract
     * @param $concrete
     *
     * @return mixed
     *
     * @since 1.3.0
     */
    private function addInstance($abstract, $concrete)
    {
        $this->instances[$abstract] = $concrete;

        return $this->instances[$abstract];
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
        return (isset($this->instances[$id]) || array_key_exists($id, $this->instances));
    }
}
