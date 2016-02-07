<?php
namespace MJanssen\Provider;

use Silex\Application;
use Symfony\Component\Routing\RouteCollection;

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
            'foo' => array(
                'pattern' => '/foo',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            'baz' => array(
                'pattern' => '/baz',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            array(
                'name' => 'fez',
                'pattern' => '/fez',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            array(
                'pattern' => '/yez',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            )
        );

        $app->register(new RoutingServiceProvider);

        $routes = $app['controllers']->flush();

        $this->assertCount(4, $routes);

        $iterator = $routes->getIterator();

        $this->assertEquals('foo', $iterator->key());

        $iterator->next();

        $this->assertEquals('baz', $iterator->key());

        $iterator->next();

        $this->assertEquals('fez', $iterator->key());

        $iterator->next();

        $this->assertEquals('GET_POST_PUT_DELETE_OPTIONS_HEAD_yez', $iterator->key());
    }

    /**
     * test if multiple routes can be added
     */
    public function testAddRoutes()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $routes = array(
            'foo' => array(
                'pattern' => '/foo',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            'baz' => array(
                'pattern' => '/baz',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            array(
                'name' => 'fez',
                'pattern' => '/fez',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            ),
            array(
                'pattern' => '/yez',
                'controller' => 'MJanssen\Controller\FooController::fooAction',
                'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
            )

        );

        $routingServiceProvider->addRoutes($app, $routes);
        $routes = $app['controllers']->flush();

        $this->assertCount(4, $routes);

        $iterator = $routes->getIterator();

        $this->assertEquals('foo', $iterator->key());

        $iterator->next();

        $this->assertEquals('baz', $iterator->key());

        $iterator->next();

        $this->assertEquals('fez', $iterator->key());

        $iterator->next();

        $this->assertEquals('GET_POST_PUT_DELETE_OPTIONS_HEAD_yez', $iterator->key());
    }

    /**
     * test if single route can be added
     */
    public function testAddRoute()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $route = array(
            'name' => 'foo',
            'pattern' => '/foo',
            'controller' => 'MJanssen\Controller\FooController::fooAction',
            'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
        );

        $routingServiceProvider->addRoute($app, $route);
        $routes = $app['controllers']->flush();
        $this->assertCount(1, $routes);
    }

    /**
     * test if single route with string method can be added
     */
    public function testStringMethod()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $route = array(
            'name' => 'foo',
            'pattern' => '/foo',
            'controller' => 'MJanssen\Controller\FooController::fooAction',
            'method' => 'get'
        );

        $routingServiceProvider->addRoute($app, $route);
        $routes = $app['controllers']->flush();
        $it = $routes->getIterator();
        $this->assertEquals('GET', $it['foo']->getMethods()[0]);
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
     * test if a method should be a string with valid methods
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMethodType()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['method'] = 'foo';
        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * test if a method should be an array with valid methods
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
        /** @var \Silex\Route $route */
        $route = $routeCollection->getIterator()->current();

        $this->assertEquals('/foo', $route->getPath());
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
     * test if routeName matches
     */
    public function testRouteName()
    {
        $routeName = 'fooRouteName';

        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $route = $this->validRoute;
        $route['name'] = $routeName;
        $routingServiceProvider->addRoute($app, $route);

        /** @var RouteCollection $routeCollection */
        $routeCollection = $app['controllers']->flush();

        $this->assertEquals($routeName, $routeCollection->getIterator()->key());
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
        $route['after'] = array(
            function () {
                return 'foo';
            },
            function () {
                return 'baz';
            }

        );
        $routingServiceProvider->addRoute($app, $route);
    }

    public function testValidBefore()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;
        $route['before'] = array(
            function () {
                return 'foo';
            },
            function () {
                return 'baz';
            }
        );
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

    public function testAddBeforeAfterMiddlewareByArrayString()
    {
        $procedureTerminates = false;
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;

        $middlewareClassName = 'FooMiddleware1';
        $middlewareMethod = 'foo1';

        //Create a Middleware-class mock
        /** @var \PHPUnit_Framework_MockObject_MockObject $fooMiddleware */
        $fooMiddleware = $this->getMockBuilder('none')
            ->setMockClassName($middlewareClassName)
            ->setMethods(array($middlewareMethod))
            ->getMock();

        $route['before'] = [$middlewareClassName . '::' . $middlewareMethod];

        $routingServiceProvider->addRoute($app, $route);

        $procedureTerminates = true;
        $this->assertTrue($procedureTerminates, 'The procedure did not terminate, therefore the middleware was not added');

    }

    public function testAddBeforeAfterMiddlewareByString()
    {
        $procedureTerminates = false;
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;

        $middlewareClassName = 'FooMiddleware1';
        $middlewareMethod = 'foo1';

        //Create a Middleware-class mock
        /** @var \PHPUnit_Framework_MockObject_MockObject $fooMiddleware */
        $fooMiddleware = $this->getMockBuilder('none')
            ->setMockClassName($middlewareClassName)
            ->setMethods(array($middlewareMethod))
            ->getMock();

        $route['before'] = $middlewareClassName . '::' . $middlewareMethod;

        $routingServiceProvider->addRoute($app, $route);

        $procedureTerminates = true;
        $this->assertTrue($procedureTerminates, 'The procedure did not terminate, therefore the middleware was not added');

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddBeforeAfterMiddlewareInvalidArgumentException()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;

        $middlewareClassName = 'FooMiddleware2';
        $middlewareMethod = 'foo2';

        //Create a Middleware-class mock
        /** @var \PHPUnit_Framework_MockObject_MockObject $fooMiddleware */
        $fooMiddleware = $this->getMockBuilder('none')
            ->setMockClassName($middlewareClassName)
            ->setMethods(array($middlewareMethod))
            ->getMock();

        $route['before'] = [$middlewareClassName . ':' . $middlewareMethod]; //no valid callback

        $routingServiceProvider->addRoute($app, $route);
    }

    /**
     * @expectedException \BadMethodCallException
     * @group test
     */
    public function testAddBeforeAfterMiddlewareBadMethodCallException()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();
        $route = $this->validRoute;

        $middlewareClassName = 'FooMiddleware3';
        $middlewareMethod = 'foo3';

        //Create a Middleware-class mock
        /** @var \PHPUnit_Framework_MockObject_MockObject $fooMiddleware */
        $fooMiddleware = $this->getMockBuilder('none')
            ->setMockClassName($middlewareClassName)
            ->getMock();

        $route['before'] = [$middlewareClassName . '::' . $middlewareMethod];

        $routingServiceProvider->addRoute($app, $route);

    }
}
