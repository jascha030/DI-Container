<?php

namespace Jascha030\DIC\Resolver\Definition;

use Closure;
use Exception;
use Jascha030\DIC\Container\Psr\PsrServiceContainer;
use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Jascha030\DIC\Exception\Definition\DefinitionTypeNotFoundException;
use Jascha030\DIC\Exception\Resolver\ResolverNotFoundException;
use Jascha030\DIC\Resolver\DefinitionResolverInterface;
use Jascha030\DIC\Resolver\Object\ObjectResolver;

/**
 * Class DefinitionResolver
 *
 * @package Jascha030\DIC\Resolver
 * @since 1.3.0
 */
class DefinitionResolver implements DefinitionResolverInterface
{
    /**
     * @var array[string] DefinitionInterface
     */
    protected $defined = [];

    public $container;

    private $availableResolvers = [
        ObjectDefinition::class => ObjectResolver::class
    ];

    /**
     * DefinitionResolver constructor.
     *
     * @param PsrServiceContainer|null $container
     * @param array $definitions
     *
     * @throws DefinitionTypeNotFoundException
     */
    public function __construct(PsrServiceContainer $container = null, array $definitions = [])
    {
        if ($container) {
            $this->container = $container;
        }

        foreach ($definitions as $definitionName => $definition) {
            if (! is_string($definitionName)) {
                $definitionName = $definition;
            }

            $this->setDefinition($definitionName, $definition);
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
     * @return DefinitionInterface
     * @throws DefinitionTypeNotFoundException
     */
    public function getDefinition($definitionName): DefinitionInterface
    {
        $this->setDefinition($definitionName);

        return call_user_func($this->defined[$definitionName]);
    }

    /**
     * Resolve requested definition
     *
     * @param DefinitionInterface $definition
     *
     * @return mixed
     * @throws Exception
     */
    public function resolve(DefinitionInterface $definition)
    {
        $definitionType = get_class($definition);
        $resolver       = $this->getResolver($definitionType);

        return $resolver->resolve($definition);
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
        return (isset($this->defined[$definitionName]) || array_key_exists($definitionName, $this->defined))
               && $this->defined[$definitionName] instanceof DefinitionInterface;
    }

    /**
     * @param $definitionName
     *
     * @return Closure
     * @throws DefinitionTypeNotFoundException
     */
    private function getDefinitionType($definitionName): Closure
    {
        if (! class_exists($definitionName) && ! interface_exists($definitionName)) {
            throw new DefinitionTypeNotFoundException(
                sprintf("No valid definition type was found for \"%s\"", $definitionName)
            );
        }

        return function () use ($definitionName) {
            return ObjectDefinition::define($definitionName);
        };
    }


    /**
     * @param string $definitionType
     *
     * @return DefinitionResolverInterface
     * @throws ResolverNotFoundException
     * @since 1.5.0
     */
    private function getResolver(string $definitionType): DefinitionResolverInterface
    {
        if (! array_key_exists($definitionType, $this->availableResolvers)) {
            throw new ResolverNotFoundException(
                sprintf("No available resolver found for definition \"%s\"", $definitionType)
            );
        }

        $resolverType = $this->availableResolvers[$definitionType];

        return new $resolverType($this);
    }

    public function hasContainer()
    {
        return($this->container instanceof PsrServiceContainer);
    }
}
