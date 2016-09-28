<?php

namespace MJanssen\Route;

use InvalidArgumentException;

class Methods
{
    const ALLOWED_METHODS = ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS', 'HEAD', 'PATCH', 'PURGE', 'OPTIONS', 'TRACE', 'CONNECT'];

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @param array $methods
     */
    public function __construct(array $methods)
    {
        $methods = array_map('strtoupper', $methods);

        foreach ($methods as $method) {
            if (!in_array($method, self::ALLOWED_METHODS)) {
                throw new InvalidArgumentException('Method "' . $method . '" is not valid, only the following methods are allowed: ' . join(', ', self::ALLOWED_METHODS));
            }
        }

        $this->methods = $methods;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->methods;
    }
}