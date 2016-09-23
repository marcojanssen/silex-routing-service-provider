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
        array $methods,
        $pattern,
        $controller,
        array $asserts = [],
        array $values = [],
        $name = ''
    ) {
        Method::assert($methods);

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
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return array
     */
    public function getAsserts()
    {
        return $this->asserts;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param array $route
     * @return Route
     */
    public static function fromArray(array $route)
    {
        if (!isset($route['pattern'])) {
            throw new InvalidArgumentException('Required parameter pattern is not set.');
        }

        if (!isset($route['method'])) {
            throw new InvalidArgumentException('Required parameter method is not set.');
        }

        if (!isset($route['controller'])) {
            throw new InvalidArgumentException('Required parameter controller is not set.');
        }

        if (isset($route['value']) && !is_array($route['value'])) {
            $route['value'] = [];
        }

        return new self(
            !is_array($route['method']) ? [$route['method']] : $route['method'],
            $route['pattern'],
            $route['controller'],
            isset($route['assert']) ? $route['assert'] : [],
            isset($route['value']) ? $route['value'] : [],
            isset($route['name']) ? $route['name'] : ''
        );
    }
}