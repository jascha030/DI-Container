<?php

namespace Jascha030\DIC\Resolver;

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
     * instance to resolve
     *
     * @param $name
     *
     * @return mixed
     */
    public function resolve($name);
}
