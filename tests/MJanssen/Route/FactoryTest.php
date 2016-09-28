<?php

namespace MJanssen\Provider;

use InvalidArgumentException;
use MJanssen\Route\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    private $validRoute = array(
        'pattern' => '/foo',
        'controller' => 'MJanssen\Controller\FooController::fooAction',
        'method' => ['get'],
        'scheme' => 'https',
        'value' => [
            'value1' => 'foo',
            'value2' => 'baz'
        ],
        'assert' => [
            'id' => 'regexp_id',
            'name' => 'regexp_name'
        ]
    );

    protected function setUp()
    {
        $this->factory = new Factory();
    }

    /**
     * @test
     */
    public function it_requires_methods()
    {
        $route = $this->validRoute;
        unset($route['method']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method is required.');

        Factory::fromArray($route);
    }

    /**
     * @test
     */
    public function it_requires_pattern()
    {
        $route = $this->validRoute;
        unset($route['pattern']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pattern is required.');

        Factory::fromArray($route);
    }

    /**
     * @test
     */
    public function it_requires_controller()
    {
        $route = $this->validRoute;
        unset($route['controller']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Controller is required.');

        Factory::fromArray($route);
    }
}