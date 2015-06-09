# RoutingServiceProvider

[![Latest Version](https://img.shields.io/github/release/marcojanssen/silex-routing-service-provider.svg?style=flat-square)](https://github.com/marcojanssen/silex-routing-service-provider/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/marcojanssen/silex-routing-service-provider/master.svg?style=flat-square)](https://travis-ci.org/marcojanssen/silex-routing-service-provider)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/marcojanssen/silex-routing-service-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/marcojanssen/silex-routing-service-provider/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/marcojanssen/silex-routing-service-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/marcojanssen/silex-routing-service-provider)
[![Total Downloads](https://img.shields.io/packagist/dt/marcojanssen/silex-routing-service-provider.svg?style=flat-square)](https://packagist.org/packages/marcojanssen/silex-routing-service-provider)

**RoutingServiceProvider** is a silex provider for easily adding routes

## Features ##

- Register providers through configuration
- Register multiple providers with the provider
- Register a single provider with the provider

## Installing

Via Composer

```
composer require marcojanssen/silex-routing-service-provider
```

## Options

Each route is required to have the following parameters:
* pattern (string) 
* controller (string)
* method - get, put, post, delete, options, head (array)

Optionally, you can set a route name (for [named routes](http://silex.sensiolabs.org/doc/usage.html#named-routes)). The key of the $route-array will be used as the route name or you can set it like this:

```php
$routes = array(
    'foo' => array(
        //'name' => 'foo', --> you can omit the name if a key is set
        'pattern' => '/foo',
        'controller' => 'Foo\Controller\FooController::fooAction',
        'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
    )
);

```

Optionally the following parameters can also be added:

* value (array)

``` php
$value = array('name' => 'value')
```

* assert (array)

``` php
$assert = array('id' => '^[\d]+$')
```

* before (array)

``` php
$before = array('before' => function() {})
```

* after (array)

``` php
$after = array('after' => function() {})
```

## Usage

### Adding a single route

`index.php`
```php

use Silex\Application;
use MJanssen\Provider\RoutingServiceProvider;

$app = new Application();
$routingServiceProvider = new RoutingServiceProvider();

$route = array(
    'name' => 'foo',
    'pattern' => '/foo',
    'controller' => 'Foo\Controller\FooController::fooAction',
    'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
);

$routingServiceProvider->addRoute($app, $route);

```

### Adding multiple routes

`index.php`
```php

use Silex\Application;
use MJanssen\Provider\RoutingServiceProvider;

$app = new Application();
$routingServiceProvider = new RoutingServiceProvider();

$routes = array(
    'foo' => array(
        //'name' => 'foo', --> you can omit the route name if a key is set
        'pattern' => '/foo',
        'controller' => 'Foo\Controller\FooController::fooAction',
        'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
    ),
    'baz' => array(
        //'name' => 'baz', --> you can omit the route name if a key is set
        'pattern' => '/baz',
        'controller' => 'Baz\Controller\BazController::bazAction',
        'method' => array('get', 'post', 'put', 'delete', 'options', 'head')
    )
);

$routingServiceProvider->addRoutes($app, $route);

```
### Adding before/after middleware
To add controller middleware you can use the 'after' and 'before' key of the route configuration. The 'before' key is used to run the middleware code before the controller logic is executed, after execution of the controller logic.
Below is an example using a middleware class and how to configure this in the route config. Instead of using a middleware class you can also use a regular callback.

**Note** Be aware that currently there is only support for php.

#### Example middleware class:

```php
class MiddleWare {

    public function __invoke(Request $request, Application $app)
    {
        //do stuff
        $x = 1;
    }
}
```

#### Using the middleware class in the route configuration


`index.php`
```php
use Silex\Application;
use MJanssen\Provider\RoutingServiceProvider;

$app = new Application();
$routingServiceProvider = new RoutingServiceProvider();

$routes = array(
    'foo' => array(
        //'name' => 'foo', --> you can omit the route name if a key is set
        'pattern' => '/foo',
        'controller' => 'Foo\Controller\FooController::fooAction',
        'method' => array('get'),
        // this is where it all happens!
        'before' => array(new MiddleWare())
    )
);
$routingServiceProvider->addRoutes($app, $route);
```

#### Adding a route middleware class via yml

You can add middleware classes via yml-/xml-configuration. Example: 

`routes.yaml`

```yaml
config.routes:
    home:
        name: 'home'
        pattern: /
        method: [ 'get', 'post' ]
        controller: 'Foo\Controllers\FooController::getAction'
        before: 'Foo\Middleware\FooController::before'
        after: 'Foo\Middleware\FooController::after'
```
The methods' interface need to match the Silex specification. Further information about route middleware classses: http://silex.sensiolabs.org/doc/middlewares.html#route-middlewares.

ATTENTION: Unfortunately with this way, you cannot use the ´__invoke´ method described above.


### Registering providers with configuration

For this example the [ConfigServiceProvider](https://github.com/igorw/ConfigServiceProvider) is used to read the yml file. The RoutingServiceProvider picks the stored configuration through the node `config.routing` as in `$app['config.routing']` by default. If you want to set a different key, add it as parameter when instantiating the RoutingServiceProvider
***Note: The key of the array will also be used as the ´route´, so you can omit ´route.

`routes.yaml`

```yaml
config.routes:
    home:
        name: 'home'
        pattern: /
        method: [ 'get', 'post' ]
        controller: 'Foo\Controllers\FooController::getAction'
```

`routes.php`

```php

return array(
    'custom.routing.key' => array(
        array(
            'pattern' => '/foo/{id}',
            'controller' => 'Foo\Controllers\FooController::getAction',
            'method' => array(
                'get'
            ),
            'assert' => array(
                'id' => '^[\d]+$'
            ),
            'value' => array(
                'value1' => 'foo',
                'value2' => 'baz'
            )
        )
    )
);

```

`index.php`
```php

use Silex\Application;
use Igorw\Silex\ConfigServiceProvider;
use MJanssen\Provider\RoutingServiceProvider;

$app = new Application();

//Set all routes
$app->register(
    new RoutingServiceProvider(__DIR__."/../app/config/routes.php")
);

//Add all routes
$app->register(new RoutingServiceProvider('custom.routing.key'));

```

**Note**: It's recommended to use php instead of yml/xml/etc, because it can be opcached. Otherwise you have to implement a caching mechanism yourself.

## Todo

convert, there is no option set this per route at the moment