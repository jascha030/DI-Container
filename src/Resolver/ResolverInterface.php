<?php

namespace Jascha030\DIC\Resolver;

use Jascha030\DIC\Definition\DefinitionInterface;

/**
 * Interface ResolverInterface
 *
 * @package Jascha030\DIC\Resolver
 * @author Jascha van Aalst
 * @since 1.1.0
 */
interface ResolverInterface
{
    /**
     * Resolve definition
     *
     * @param $definition
     *
     * @return mixed
     */
    public function resolve(DefinitionInterface $definition);
}
