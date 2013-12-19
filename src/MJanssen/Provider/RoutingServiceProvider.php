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
        $this->validateRoute($route);

        foreach($route['method'] AS $method) {
            $newRoute = $route;
            $newRoute['method'] = $method;
            $this->addRouteByMethod($app, $newRoute);
        }
    }

    /**
     * @param $route
     * @throws \InvalidArgumentException
     */
    protected function validateRoute($route)
    {
        if(!isset($route['pattern']) || !isset($route['method']) || !isset($route['controller'])) {
            throw new InvalidArgumentException('Required parameter (pattern/method/controller) is not set.');
        }

        $arrayParameters = array('method', 'assert', 'value');

        foreach($arrayParameters as $parameter) {
            if(isset($route[$parameter]) && !is_array($route[$parameter])) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not of type Array (%s)',
                    $parameter, gettype($route[$parameter])
                ));
            }
        }
    }

    /**
     * @param Application $app
     * @param $route
     */
    protected function addRouteByMethod(Application $app, $route)
    {
        $controller = $this->getController($app, $route);

        if(isset($route['value'])) {
            $this->addActions($controller, $route['value'], 'value');
        }

        if(isset($route['assert'])) {
            $this->addActions($controller, $route['assert'], 'assert');
        }

        if(isset($route['before'])) {
            $controller->before($route['before']);
        }

        if(isset($route['after'])) {
            $controller-after($route['after']);
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
     * @return Controller
     */
    protected function getController(Application $app, $route)
    {
        $availableMethods = array('get', 'put', 'post', 'delete', 'options', 'head');
        if(!in_array(strtolower($route['method']), $availableMethods)) {
            throw new InvalidArgumentException('Method is not valid, only the following methods are allowed: get, put, post, delete');
        }

        if('options' !== $route['method'] && 'head' !== $route['method']) {
            return call_user_func_array(array($app, strtolower($route['method'])), array($route['pattern'], $route['controller']));
        }

        return $app->match($route['pattern'], $route['controller'])->method(strtoupper($route['method']));
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
            foreach ($actions as $name => $value) {
                $this->addAction($controller, $name, $value, $type);
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
}
