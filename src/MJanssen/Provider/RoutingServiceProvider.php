<?php
namespace MJanssen\Provider;

use InvalidArgumentException;
use MJanssen\Route\Name;
use MJanssen\Route\Route;
use RuntimeException;
use Silex\Application;
use Silex\Controller;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package MJanssen\Provider
 */
class RoutingServiceProvider implements
    ServiceProviderInterface,
    BootableProviderInterface,
    EventListenerProviderInterface
{
    /**
     * @var string
     */
    protected $routingContainerId;

    /**
     * @param string $routingContainerId
     */
    public function __construct($routingContainerId = 'config.routes')
    {
        $this->routingContainerId = $routingContainerId;
    }

    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        if (!$container->offsetExists($this->routingContainerId)) {
            throw new RuntimeException('Routing container id not set');
        }

        $routes = $container->offsetGet($this->routingContainerId);

        if (!is_array($routes)) {
            throw new InvalidArgumentException(
                sprintf('Supplied routes in container with id %s must be of type Array', $this->routingContainerId)
            );
        }

        $this->addRoutes($container, $routes);
    }

    /**
     * @param Application $app
     * @codeCoverageIgnore
     */
    public function boot(Application $app)
    {
    }

    /**
     * @param Container $container
     * @param EventDispatcherInterface $dispatcher
     * @codeCoverageIgnore
     */
    public function subscribe(Container $container, EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Add routes
     *
     * @param Container $container
     * @param array $routes
     */
    public function addRoutes(Container $container, array $routes)
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($container, $route, $name);
        }
    }

    /**
     * Adds a route, a given route-name (for named routes) and all of its methods
     *
     * @param Container $app
     * @param array $route
     * @throws InvalidArgumentException
     */
    public function addRoute(Container $container, array $route, $name = '')
    {
        $route2 = Route::fromArray($route);

        if ($route2->getName()) {
            $name = $route2->getName();
        }

        $name = new Name($name);

        $controller = $container->match($route2->getPattern(), $route2->getController())
            ->bind((string) $name)
            ->method(
                join('|', array_map('strtoupper', $route2->getMethods()))
            );

        $supportedProperties = array('value', 'assert', 'convert', 'before', 'after');
        foreach ($supportedProperties AS $property) {
            if (isset($route[$property])) {
                $this->addActions($controller, $route[$property], $property);
            }
        }

        if (isset($route['scheme'])) {
            if ('https' === $route['scheme']) {
                $controller->requireHttps();
            }
        }
    }

    /**
     * Sanitizes the routeName for named route:
     *
     * - replaces '/', ':', '|', '-' with '_'
     * - removes special characters
     *
     *  Algorithm copied from \Silex\Controller->generateRouteName
     *  see: https://github.com/silexphp/Silex/blob/1.2/src/Silex/Controller.php
     *
     * @param string $routeName
     * @return string
     */
    protected function sanitizeRouteName($routeName)
    {
        if (empty($routeName)) {
            //If no routeName is specified,
            //we set an empty route name to force the default route name e.g. "GET_myRouteName"
            return '';
        }

        $routeName = str_replace(array('/', ':', '|', '-'), '_', $routeName);
        $routeName = preg_replace('/[^a-z0-9A-Z_.]+/', '', $routeName);

        return $routeName;
    }

    /**
     * @param Controller $controller
     * @param $actions
     * @param $type
     * @throws \InvalidArgumentException
     */
    protected function addActions(Controller $controller, $actions, $type)
    {
        if (!is_array($actions)){
            if ($type === 'before' || $type === 'after') {
                $actions = array($actions);
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Action %s is not of type Array (%s)',
                        $type, gettype($actions)
                    )
                );
            }
        }

        foreach ($actions as $name => $value) {
            switch ($type) {
                case 'after':
                    $this->addBeforeAfterMiddleware($controller, $type, $value);
                    break;
                case 'before':
                    $this->addBeforeAfterMiddleware($controller, $type, $value);
                    break;
                default:
                    $this->addAction($controller, $name, $value, $type);
                    break;
            }
        }
    }

    /**
     * @param Controller $controller
     * @param $name
     * @param $value
     * @param $type
     */
    protected function addAction(Controller $controller, $name, $value, $type)
    {
        call_user_func_array(array($controller, $type), array($name, $value));
    }

    protected function isClosure($param)
    {
        return is_object($param) && ($param instanceof \Closure);
    }

    /**
     * Adds a middleware (before/after)
     *
     * @param Controller $controller
     * @param string $type | 'before' or 'after'
     * @param $value
     */
    protected function addBeforeAfterMiddleware(Controller $controller, $type, $value)
    {
        $supportedMWTypes = ['before', 'after'];

        if (!in_array($type, $supportedMWTypes)) {
            throw new \UnexpectedValueException(
                sprintf(
                    'type %s not supported',
                    $type
                )
            );
        }

        if ($this->isClosure($value)) {
            //When a closure is provided, we will just load it as a middleware type
            $controller->$type($value);
        } else {
            //In this case a yaml/xml configuration was used
            $this->addMiddlewareFromConfig($controller, $type, $value);
        }
    }

    /**
     * Adds a before/after middleware by its configuration
     *
     * @param Controller $controller
     * @param $type
     * @param $value
     */
    protected function addMiddlewareFromConfig(Controller $controller, $type, $value)
    {
        if (!is_string($value) || strpos($value, '::') === FALSE) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s is no valid Middleware callback. Please provide the following syntax: NamespaceName\SubNamespaceName\ClassName::methodName',
                    $value
                )
            );
        }

        list($class, $method) = explode('::', $value, 2);

        if ($class && $method) {

            if (!method_exists($class, $method)) {
                throw new \BadMethodCallException(sprintf('Method "%s::%s" does not exist.', $class, $method));
            }

            $controller->$type([new $class, $method]);
        }
    }
}
