<?php

namespace Jascha030\DIC\Resolver;

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
     * @param DefinitionResolver $definition
     *
     * @return mixed
     * @since 1.1.0
     */
    public function resolve(DefinitionResolver $definition)
    {
    }
}
