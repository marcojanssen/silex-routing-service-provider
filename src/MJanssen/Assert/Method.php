<?php

namespace MJanssen\Assert;

use InvalidArgumentException;

class Method
{
    const ALLOWED_METHODS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'purge', 'options', 'trace', 'connect'];

    public static function assert(array $methods)
    {
        $methods = array_map('strtolower', $methods);

        foreach ($methods as $method) {
            if (!in_array($method, self::ALLOWED_METHODS)) {
                throw new InvalidArgumentException('Method "' . $method . '" is not valid, only the following methods are allowed: ' . join(', ', self::ALLOWED_METHODS));
            }
        }
    }
}