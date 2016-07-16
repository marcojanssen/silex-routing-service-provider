<?php
namespace MJanssen\Provider;

use InvalidArgumentException;
use Silex\Application;
use Silex\Controller;
use Silex\Route;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RoutingServiceProvider
 * @package MJanssen\Provider
 */
class RoutingServiceProvider implements
    ServiceProviderInterface,
    BootableProviderInterface,
    EventListenerProviderInterface
{
    /**
     * @var
     */
    protected $appRoutingKey;

    /**
     * @param string $appRoutingKey
     */
    public function __construct($appRoutingKey = 'config.routes')
    {
        $this->appRoutingKey = $appRoutingKey;
    }

    /**
     * @param Container $app
     * @throws \InvalidArgumentException
     */
    public function register(Container $app)
    {
        if (isset($app[$this->appRoutingKey])) {
            if (is_array($app[$this->appRoutingKey])) {
                $this->addRoutes($app, $app[$this->appRoutingKey]);
            } else {
                throw new InvalidArgumentException('config.routes must be of type Array');
            }
        }
    }

    /**
     * @param Application $app
     * @codeCoverageIgnore
     */
    public function boot(Application $app)
    {
    }

    /**
     * @param Container $app
     * @param EventDispatcherInterface $dispatcher
     * @codeCoverageIgnore
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Adds all routes
     *
     * @param Container $app
     * @param $routes
     */
    public function addRoutes(Container $app, $routes)
    {
        foreach ($routes as $name => $route) {

            if (is_numeric($name)) {
                $name = '';
            }

            $this->addRoute($app, $route, $name);
        }
    }

    /**
     * Adds a route, a given route-name (for named routes) and all of its methods
     *
     * @param Container $app
     * @param array $route
     * @throws InvalidArgumentException
     */
    public function addRoute(Container $app, array $route, $name = '')
    {
        if (isset($route['method']) && is_string($route['method'])) {
            $route['method'] = array($route['method']);
        }

        $this->validateRoute($route);

        if (array_key_exists('name', $route)) {
            $name = $route['name'];
        }

        $controller = $app->match(
            $route['pattern'],
            $route['controller'])
            ->bind(
                $this->sanitizeRouteName($name)
            )->method(
                join('|', array_map('strtoupper', $route['method']))
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
     * Validates the given methods. Only get, put, post, delete, options, head
     * are allowed
     *
     * @param array $methods
     */
    protected function validateMethods(Array $methods)
    {
        $availableMethods = array('get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'purge', 'options', 'trace', 'connect');
        foreach (array_map('strtolower', $methods) as $method) {
            if (!in_array($method, $availableMethods)) {
                throw new InvalidArgumentException('Method "' . $method . '" is not valid, only the following methods are allowed: ' . join(', ', $availableMethods));
            }
        }
    }

    /**
     * Validates the given $route Array
     *
     * @param $route
     * @throws \InvalidArgumentException
     */
    protected function validateRoute($route)
    {
        if (!isset($route['pattern']) || !isset($route['method']) || !isset($route['controller'])) {
            throw new InvalidArgumentException('Required parameter (pattern/method/controller) is not set.');
        }

        $arrayParameters = array('method', 'assert', 'value');

        foreach ($arrayParameters as $parameter) {
            if (isset($route[$parameter]) && !is_array($route[$parameter])) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not of type Array (%s)',
                    $parameter, gettype($route[$parameter])
                ));
            }
        }

        $this->validateMethods($route['method']);
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
