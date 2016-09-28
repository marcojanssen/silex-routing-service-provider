<?php
namespace MJanssen\Provider;

use InvalidArgumentException;
use RuntimeException;
use Silex\Application;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutingServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    const NAME = 'test';
    const PATTERN = '/test';
    const CONTROLLER = 'MJanssen\Controller\FooController::fooAction';

    private $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD'];

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

    /**
     * @test
     */
    public function it_adds_multiple_routes_through_application()
    {
        $app = new Application();

        $app['config.routes'] = [
            'test1' => [
                'pattern' => '/test1',
                'controller' => 'MJanssen\Controller\FooController::test1Action',
                'method' => ['GET']
            ],
            'test2' => [
                'pattern' => '/test2',
                'controller' => 'MJanssen\Controller\FooController::test2Action',
                'method' => ['GET']
            ],
            'test3' => [
                'pattern' => '/test3',
                'controller' => 'MJanssen\Controller\FooController::test3Action',
                'method' => ['GET']
            ],
        ];

        $app->register(new RoutingServiceProvider());

        /** @var RouteCollection $routes */
        $routes = $app['controllers']->flush();

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function it_adds_multiple_routes()
    {
        $app = new Application();
        $routingServiceProvider = new RoutingServiceProvider();

        $routes = [
            'test1' => [
                'pattern' => '/test1',
                'controller' => 'MJanssen\Controller\FooController::test1Action',
                'method' => ['GET']
            ],
            'test2' => [
                'pattern' => '/test2',
                'controller' => 'MJanssen\Controller\FooController::test2Action',
                'method' => ['GET']
            ],
            'test3' => [
                'pattern' => '/test3',
                'controller' => 'MJanssen\Controller\FooController::test3Action',
                'method' => ['GET']
            ],
        ];

        $routingServiceProvider->addRoutes($app, $routes);
        $routes = $app['controllers']->flush();

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function it_adds_a_route()
    {
        $routingServiceProvider = new RoutingServiceProvider();

        $routingServiceProvider->addRoute(
            $app = new Application(),
            [
                'name' => self::NAME,
                'pattern' => self::PATTERN,
                'controller' => self::CONTROLLER,
                'method' => $this->methods
            ]
        );
        $routes = $app['controllers']->flush();
        $this->assertCount(1, $routes);

        /** @var Route $route */
        $route = $routes->get(self::NAME);

        $this->assertSame(
            self::PATTERN,
            $route->getPath()
        );

        $this->assertSame(
            $this->methods,
            $route->getMethods()
        );

        $this->assertSame(
            self::CONTROLLER,
            $route->getDefault('_controller')
        );
    }

    /**
     * @test
     */
    public function it_adds_a_route_with_single_method_as_string()
    {
        $routingServiceProvider = new RoutingServiceProvider();

        $routingServiceProvider->addRoute(
            $app = new Application(),
            [
                'name' => self::NAME,
                'pattern' => self::PATTERN,
                'controller' => self::CONTROLLER,
                'method' => 'get'
            ]
        );
        $routes = $app['controllers']->flush();
        $this->assertCount(1, $routes);

        $this->assertSame(
            ['GET'],
            $routes->get(self::NAME)->getMethods()
        );
    }

    /**
     * @test
     */
    public function it_adds_a_named_route()
    {
        $routingServiceProvider = new RoutingServiceProvider();

        $expectedName = 'expectedName';
        $secondExpectedName = 'secondExpectedName';

        $routingServiceProvider->addRoutes(
            $app = new Application(), [
                $expectedName => [
                    'pattern' => self::PATTERN,
                    'controller' => self::CONTROLLER,
                    'method' => 'GET'
                ],
                self::NAME => [
                    'name' => $secondExpectedName,
                    'pattern' => '/other-pattern',
                    'controller' => '',
                    'method' => 'GET'
                ],
        ]);

        /** @var RouteCollection $routes */
        $routes = $app['controllers']->flush();
        $this->assertCount(2, $routes);

        $this->assertInstanceOf(
            Route::class,
            $routes->get($expectedName)
        );

        $this->assertInstanceOf(
            Route::class,
            $routes->get($secondExpectedName)
        );
    }

    /**
     * @test
     */
    public function it_sets_a_default_name_for_a_route()
    {
        $routingServiceProvider = new RoutingServiceProvider();

        $routingServiceProvider->addRoute(
            $app = new Application(),
            [
                'pattern' => self::PATTERN,
                'controller' => self::CONTROLLER,
                'method' => ['GET', 'POST']
            ]
        );
        $routes = $app['controllers']->flush();
        $this->assertCount(1, $routes);

        $this->assertInstanceOf(
            Route::class,
            $routes->get('GET_POST_test')
        );
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_triggers_when_no_container_id_is_set()
    {
        $app = new Application();
        $app->register(new RoutingServiceProvider);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_triggers_when_no_routes_are_set()
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
