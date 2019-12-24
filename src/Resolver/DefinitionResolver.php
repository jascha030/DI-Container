<?php

namespace Jascha030\DIC\Resolver;

use Jascha030\DIC\Definition\DefinitionInterface;
use Psr\Container\ContainerInterface;

class DefinitionResolver implements ResolverInterface
{
    private $container;

    private $resolvers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve requested definition
     *
     * @param DefinitionInterface $definition
     *
     * @return mixed
     * @since 1.1.0
     */
    public function resolve(DefinitionInterface $definition)
    {
    }
}
