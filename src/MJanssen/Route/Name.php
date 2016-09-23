<?php

namespace MJanssen\Route;

class Name
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name = '')
    {
        if (empty($name)) {
            //If no routeName is specified,
            //we set an empty route name to force the default route name e.g. "GET_myRouteName"
            $name = '';
        }

        if (is_numeric($name)) {
            $name = '';
        }

        $name = str_replace(['/', ':', '|', '-'], '_', $name);
        $name = preg_replace('/[^a-z0-9A-Z_.]+/', '', $name);

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
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}