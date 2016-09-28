<?php

namespace MJanssen\Route;

use InvalidArgumentException;

class Factory
{
    /**
     * @param array $route
     *
     * @return Route
     */
    public static function fromArray(array $route)
    {
        if (!isset($route['pattern'])) {
            throw new InvalidArgumentException('Pattern is required.');
        }

        if (!isset($route['method'])) {
            throw new InvalidArgumentException('Method is required.');
        }

        if (!isset($route['controller'])) {
            throw new InvalidArgumentException('Controller is required.');
        }

        if (isset($route['value']) && !is_array($route['value'])) {
            $route['value'] = [];
        }

        $methods = !is_array($route['method']) ? [$route['method']] : $route['method'];
        $assert = isset($route['assert']) ? $route['assert'] : [];
        $value = isset($route['value']) ? $route['value'] : [];
        $name = isset($route['name']) ? $route['name'] : '';

        return new Route(
            new Methods($methods),
            new Pattern($route['pattern']),
            new Controller($route['controller']),
            new Asserts($assert),
            new Values($value),
            new Name($name)
        );
    }
}