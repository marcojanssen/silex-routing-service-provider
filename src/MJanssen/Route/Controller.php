<?php

namespace MJanssen\Route;

class Controller
{
    /**
     * @var string
     */
    private $controller;

    /**
     * @param string $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getController();
    }
}