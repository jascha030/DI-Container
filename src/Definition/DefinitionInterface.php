<?php

namespace Jascha030\DIC\Definition;

use Closure;
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
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function setName(string $name);
}
