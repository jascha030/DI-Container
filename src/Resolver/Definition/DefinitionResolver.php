<?php

namespace Jascha030\DIC\Resolver\Definition;

use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Jascha030\DIC\Exception\Definition\DefinitionNotFoundException;
use Jascha030\DIC\Exception\Definition\DefinitionTypeNotFoundException;
use Jascha030\DIC\Resolver\ResolverInterface;

/**
 * Class DefinitionResolver
 *
 * @package Jascha030\DIC\Resolver
 * @since 1.3.0
 */
class DefinitionResolver implements ResolverInterface
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
     * DefinitionResolver constructor.
     *
     * @param array $definitions
     *
     * @throws DefinitionTypeNotFoundException
     */
    public function __construct(array $definitions)
    {
        $this->definitions = array_merge($this->definitions, $definitions);

        foreach ($this->definitions as $className => $definition) {
            if (! is_string($className)) {
                $className = $definition;
            }
            $this->setDefinition($className, $definition);
        }
    }

    /**
     * @param $definitionName
     * @param DefinitionInterface|null $definition
     *
     * @throws DefinitionTypeNotFoundException
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
     * @param $definitionName
     *
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws DefinitionTypeNotFoundException
     */
    public function resolve($definitionName)
    {
        if (! $this->isDefined($definitionName)) {
            $this->setDefinition($definitionName);
        }

        $definition = $this->getDefinition($definitionName);

        return $this->resolveDefinition($definition);
    }

    /**
     * Resolve requested definition
     *
     * @param DefinitionInterface $definition
     *
     * @return mixed
     */
    protected function resolveDefinition(DefinitionInterface $definition)
    {
        return $definition->resolve($this);
    }

    /**
     * Check if definition is already defined
     *
     * @param $definitionName
     *
     * @return bool
     */
    private function isDefined($definitionName)
    {
        return (isset($this->defined[$definitionName]) || array_key_exists($definitionName, $this->definitions))
               && $this->defined[$definitionName] instanceof DefinitionInterface;
    }

    /**
     * @param $definitionName
     *
     * @return DefinitionInterface
     * @throws DefinitionTypeNotFoundException
     */
    private function getDefinitionType($definitionName): DefinitionInterface
    {
        if (class_exists($definitionName) || interface_exists($definitionName)) {
            return ObjectDefinition::define($definitionName);
        }

        throw new DefinitionTypeNotFoundException(
            sprintf("No valid definition type was found for \"%s\"", $definitionName)
        );
    }

    /**
     * @param $definitionName
     *
     * @return DefinitionInterface
     * @throws DefinitionNotFoundException
     */
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
