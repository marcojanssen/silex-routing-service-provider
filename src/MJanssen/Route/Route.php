<?php

namespace MJanssen\Route;

use InvalidArgumentException;
use MJanssen\Assert\Method;

class Route
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var array
     */
    private $asserts = [];

    /**
     * @var array
     */
    private $values = [];

    /**
     * @param array $methods
     * @param string $pattern
     * @param string $controller
     * @param array $asserts
     * @param array $values
     * @param string $name
     */
    public function __construct(
        Methods $methods,
        Pattern $pattern,
        Controller $controller,
        Asserts $asserts = null,
        Values $values = null,
        Name $name
    ) {
        $this->methods = $methods;
        $this->pattern = $pattern;
        $this->controller = $controller;
        $this->asserts = $asserts;
        $this->values = $values;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Methods
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return Pattern
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return Asserts
     */
    public function getAsserts()
    {
        return $this->asserts;
    }

    /**
     * @return Values
     */
    public function getValues()
    {
        return $this->values;
    }
}