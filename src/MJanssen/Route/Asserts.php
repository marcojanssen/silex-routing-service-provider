<?php

namespace MJanssen\Route;

class Asserts
{
    /**
     * @var array
     */
    private $asserts;

    /**
     * @param array $asserts
     */
    public function __construct(array $asserts = [])
    {
        $this->asserts = $asserts;
    }

    /**
     * @return array
     */
    public function getAsserts()
    {
        return $this->asserts;
    }
}