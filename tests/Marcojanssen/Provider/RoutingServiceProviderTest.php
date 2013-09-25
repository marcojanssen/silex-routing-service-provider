<?php
namespace Marcojanssen\Provider;

use Silex\Application;
use Marcojanssen\Provider\RoutingServiceProvider;

class RoutingServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    private $validRoute = array(
        'pattern' => '/foo',
        'controller' => 'Marcojanssen\Controller\FooController::fooAction',
        'method' => array('get'),
        'scheme' => 'https',
        'value' => array(
            array('value1' => 'foo'),
            array('value2' => 'baz')
        ),
        'assert' => array(
            array('id' => 'regexp_id'),
            array('name' => 'regexp_name')
        ),
        'convert' => array(
            array('id' => 'number'),
            array('name' => 'string')
        )
    );

    /**
     * test if multiple routes can be added through application
     */
    public function testApplicationRoutes()
    {
        $app = new Application();

        $app['config.routes'] = array(
            array(
                'pattern' => '/foo',
                'controller' => 'Marcojanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete')
            ),
            array(
                'pattern' => '/baz',
                'controller' => 'Marcojanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete')
            )
        );

        $app->register(new RoutingServiceProvider);

        $routes = $app['controllers']->flush();

        $this->assertCount(8, $routes);
    }

    /**
     * test if multiple routes can be added
    */
    public function testAddRoutes()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $routes = array(
            array(
                'pattern' => '/foo',
                'controller' => 'Marcojanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete')
            ),
            array(
                'pattern' => '/baz',
                'controller' => 'Marcojanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete')
            )
        );

        $routingServiceProvider->addRoutes($app, $routes);
        $routes = $app['controllers']->flush();

        $this->assertCount(8, $routes);
    }

    /**
     * test if single route can be added
     */
    public function testAddRoute()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $route = array(
            'pattern' => '/foo',
            'controller' => 'Marcojanssen\Controller\FooController::fooAction',
            'method' => array('get', 'post', 'put', 'delete')
        );

        $routingServiceProvider->addRoute($app, $route);
        $routes = $app['controllers']->flush();

        $this->assertCount(4, $routes);
    }

    /**
     * test if invalid application config is triggerd
     * @expectedException InvalidArgumentException
     */
    public function testInvalidApplicationConfiguration()
    {
        $app = new Application();
        $app['config.routes'] = '';
        $app->register(new RoutingServiceProvider);
    }

    /**
     * test if method is required
     * @expectedException InvalidArgumentException
     */
    public function testRequiredParameterMethod()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        unset($route['method']);
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * test if a method should be an array
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMethodType()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['method'] = 'get';
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * test if controller parameter is required
     * @expectedException InvalidArgumentException
     */
    public function testRequiredControllerParameter()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        unset($route['controller']);
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * test if controller parameter is required
     * @expectedException InvalidArgumentException
     */
    public function testRequiredPatternParameter()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        unset($route['pattern']);
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * test if method is required
     * @expectedException InvalidArgumentException
     */
    public function testInvalidValues()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['value'] = '';
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * test if scheme can be set to https
     */
    public function testRouteScheme()
    {
        $routeCollection = $this->getValidRoute();
        $route = $routeCollection->getIterator()->current();

        $this->assertEquals('https', $route->getSchemes()[0]);
    }

    /**
     * test if pattern matches
     */
    public function testRoutePattern()
    {
        $routeCollection = $this->getValidRoute();
        $route = $routeCollection->getIterator()->current();

        $this->assertEquals('/foo', $route->getPattern());
    }

    /**
     * test if controller matches
     */
    public function testRouteController()
    {
        $routeCollection = $this->getValidRoute();
        $route = $routeCollection->getIterator()->current();
        $defaults = $route->getDefaults();

        $this->assertEquals('Marcojanssen\Controller\FooController::fooAction', $defaults['_controller']);
    }

    /**
     * test if values matches
     */
    public function testRouteValues()
    {
        $routeCollection = $this->getValidRoute();
        $route = $routeCollection->getIterator()->current();
        $defaults = $route->getDefaults();

        $this->assertEquals('foo', $defaults['value1']);
        $this->assertEquals('baz', $defaults['value2']);
    }

    /**
     * test if assert matches
     */
    public function testRouteAssert()
    {
        $routeCollection = $this->getValidRoute();
        $route = $routeCollection->getIterator()->current();
        $requirements = $route->getRequirements();

        $this->assertEquals('regexp_id', $requirements['id']);
        $this->assertEquals('regexp_name', $requirements['name']);
    }

    /**
     * test if convert matches
     */
    public function testRouteConvert()
    {
        $routeCollection = $this->getValidRoute();
        $route = $routeCollection->getIterator()->current();
        $options = $route->getOptions();

        $this->assertEquals('number', $options['_converters']['id']);
        $this->assertEquals('string', $options['_converters']['name']);
    }

    /**
     * @return mixed
     */
    protected function getValidRoute()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $routingServiceProvider->addRoute($app, $route);

        return $app['controllers']->flush();
    }
}
