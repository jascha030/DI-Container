<?php

namespace Jascha030\DIC\Definition;

use Jascha030\DIC\Resolver\DefinitionResolverInterface;

/**
 * Interface DefinitionInterface
 *
 * @package Jascha030\DIC\Definition
 * @author Jascha van Aalst
 * @since 1.1.0
 */
interface DefinitionInterface
{
    /**
     * Resolve definition type
     *
     * @param DefinitionResolverInterface $resolver
     *
     * @return mixed
     */
    public function resolve(DefinitionResolverInterface $resolver): \Closure;
}
