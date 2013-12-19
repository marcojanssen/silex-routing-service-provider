<?php
namespace MJanssen\Provider;

use Silex\Application;
use MJanssen\Provider\RoutingServiceProvider;

class RoutingServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    private $validRoute = array(
        'pattern' => '/foo',
        'controller' => 'MJanssen\Controller\FooController::fooAction',
        'method' => array('get'),
        'scheme' => 'https',
        'value' => array(
            'value1' => 'foo',
            'value2' => 'baz'
        ),
        'assert' => array(
            'id' => 'regexp_id',
            'name' => 'regexp_name'
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
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            array(
                'pattern' => '/baz',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            )
        );

        $app->register(new RoutingServiceProvider);

        $routes = $app['controllers']->flush();

        $this->assertCount(12, $routes);
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
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            array(
                'pattern' => '/baz',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            )
        );

        $routingServiceProvider->addRoutes($app, $routes);
        $routes = $app['controllers']->flush();

        $this->assertCount(12, $routes);
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
            'controller' => 'MJanssen\Controller\FooController::fooAction',
            'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
        );

        $routingServiceProvider->addRoute($app, $route);
        $routes = $app['controllers']->flush();

        $this->assertCount(6, $routes);
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
     * test if a method should be an array
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMethodValue()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['method'] = array('foo');
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
        $schemes = $route->getSchemes();

        $this->assertEquals('https', $schemes[0]);
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

        $this->assertEquals('MJanssen\Controller\FooController::fooAction', $defaults['_controller']);
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

    public function testValidAfter()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['after'] = function() { return 'foo'; };
        $routingServiceProvider->addRoute($app, $route);
    }

    public function testValidBefore()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['before'] = function() { return 'foo'; };
        $routingServiceProvider->addRoute($app, $route);

    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAfter()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['after'] = '';
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidBefore()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['before'] = '';
        $routingServiceProvider->addRoute($app, $route);

    }
}
