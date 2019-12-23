<?php

namespace Jascha030\DIC\Definition;

use Jascha030\DIC\Resolver\ResolverInterface;

interface DefinitionInterface
{
    public function resolve(ResolverInterface $resolver);
}
