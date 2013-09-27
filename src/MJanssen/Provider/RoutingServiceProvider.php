<?php
namespace MJanssen\Provider;

use InvalidArgumentException;
use Silex\Application;
use Silex\Controller;
use Silex\ServiceProviderInterface;

/**
 * Class RoutingServiceProvider
 * @package MJanssen\Provider
 */
class RoutingServiceProvider implements ServiceProviderInterface
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
     * @param Application $app
     */
    public function register(Application $app)
    {
        if(isset($app[$this->appRoutingKey])) {
            if(is_array($app[$this->appRoutingKey])) {
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
     * @param Application $app
     * @param $routes
     */
    public function addRoutes(Application $app, $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($app, $route);
        }
    }

    /**
     * @param Application $app
     * @param $route
     * @throws InvalidArgumentException
     */
    public function addRoute(Application $app, $route)
    {
        if(!isset($route['pattern'])) {
            throw new InvalidArgumentException('Pattern is not set');
        }

        if(!isset($route['method'])) {
            throw new InvalidArgumentException('Method is not set');
        }

        if(!is_array($route['method'])) {
            throw new InvalidArgumentException(sprintf(
                'Method is not of type Array (%s)',
                gettype($route['method'])
            ));
        }

        if(!isset($route['controller'])) {
            throw new InvalidArgumentException('Controller is not set');
        }

        foreach($route['method'] AS $method) {
            $newRoute = $route;
            $newRoute['method'] = $method;
            $this->setRouteByMethod($app, $newRoute);
        }
    }

    /**
     * @param Application $app
     * @param $route
     */
    protected function setRouteByMethod(Application $app, $route)
    {
        $controller = $this->getController($app, $route);

        if(isset($route['value'])) {
            $this->addActions($controller, $route['value'], 'value');
        }

        if(isset($route['assert'])) {
            $this->addActions($controller, $route['assert'], 'assert');
        }

        if(isset($route['convert'])) {
            $this->addActions($controller, $route['convert'], 'convert');
        }

        if(isset($route['scheme'])) {
            if('https' === $route['scheme']) {
                $controller->requireHttps();
            }
        }
    }

    /**
     * @param Application $app
     * @param $route
     * @return \Silex\Controller
     */
    protected function getController(Application $app, $route)
    {
        $availableMethods = array('get', 'put', 'post', 'delete');
        if(!in_array($route['method'], $availableMethods)) {
            throw new InvalidArgumentException('Method is not valid, only the following methods are allowed: get, put, post, delete');
        }

        return call_user_func_array(array($app, $route['method']), array($route['pattern'], $route['controller']));
    }

    /**
     * @param Controller $controller
     * @param $actions
     * @param $type
     */
    protected function addActions(Controller $controller, $actions, $type)
    {
        if(!is_array($actions)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Action %s is not of type Array (%s)',
                    $type, gettype($actions)
                )
            );
            $this->addAction($controller, $actions, $type);
        } else {
            foreach ($actions as $action) {
                $this->addAction($controller, $action, $type);
            }
        }
    }

    /**
     * @param Controller $controller
     * @param $action
     * @param $type
     */
    protected function addAction(Controller $controller, $action, $type)
    {
        if(is_array($action)) {
            $name = key($action);
            $value = $action[$name];
            $action = array($name, $value);
        }

        call_user_func_array(array($controller, $type), $action);
    }
}
