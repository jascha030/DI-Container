<?php

namespace Jascha030\DIC\Resolver;

use Jascha030\DIC\Definition\DefinitionInterface;
use Jascha030\DIC\Definition\ObjectDefinition;
use Psr\Container\ContainerInterface;

class Resolver implements ResolverInterface
{
    private $container;

    private $resolvers = [];

    public function __construct(ContainerInterface $container)
    {
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
    }
}
