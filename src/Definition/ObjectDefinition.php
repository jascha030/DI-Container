<?php

namespace Jascha030\DIC\Definition;

/**
 * Class ObjectDefinition
 *
 * @package Jascha030\DIC\Definition
 * @author Jascha van Aalst
 * @since 1.1.0
 */
class ObjectDefinition implements DefinitionInterface
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * ObjectDefinition constructor.
     *
     * @param $className
     */
    public function __construct($className)
    {
        $this->name = $className;
    }

    /**
     * Set definition
     *
     * @param $className
     *
     * @return ObjectDefinition
     */
    public static function define($className)
    {
        return new ObjectDefinition($className);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
}
