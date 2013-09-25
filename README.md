# RoutingServiceProvider #

-----

[![Build Status](https://travis-ci.org/marcojanssen/silex-routing-service-provider.png?branch=master)](https://travis-ci.org/marcojanssen/silex-routing-service-provider)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/marcojanssen/silex-routing-service-provider/badges/quality-score.png?s=ee8a98ec16a263e96f27ccf6be68db3d434d1156)](https://scrutinizer-ci.com/g/marcojanssen/silex-routing-service-provider/)
[![Code Coverage](https://scrutinizer-ci.com/g/marcojanssen/silex-routing-service-provider/badges/coverage.png?s=c0ad7b2616ce7c0b5e472457d7ec49063f86f527)](https://scrutinizer-ci.com/g/marcojanssen/silex-routing-service-provider/)

**RoutingServiceProvider** is a silex provider for easily adding routes

## Features ##

- Register providers through configuration
- Register multiple providers with the provider
- Register a single provider with the provider

## Installing

- Install [Composer](http://getcomposer.org)

- Add `marcojanssen/silex-routing-service-provider` to your `composer.json`:

```json
{
    "require": {
        "marcojanssen/silex-routing-service-provider": "1.*"
    }
}
```

- Install/update your dependencies

## Options

Each route is required to have the following parameters:
* pattern (string) 
* controller (string)
* method - get, put, post, delete (array)

Optionally the following parameters can also be added:
* value (array) 
``` php
$value = array('name' => 'value')
```
* assert (array)
``` php
$assert = array('id' => '^[\d]+$')
```

## Usage

### Adding a single route

`index.php`
```php

$app = new Silex\Application();
$routingServiceProvider = new MJanssen\Provider\RoutingServiceProvider();

$route = array(
    'pattern' => '/foo',
    'controller' => 'Foo\Controller\FooController::fooAction',
    'method' => array('get', 'post', 'put', 'delete')
);

$routingServiceProvider->addRoute($app, $route);

```

### Adding multiple routes

`index.php`
```php

$app = new Silex\Application();
$routingServiceProvider = new MJanssen\Provider\RoutingServiceProvider();

$routes = array(
    array(
        'pattern' => '/foo',
        'controller' => 'Foo\Controller\FooController::fooAction',
        'method' => array('get', 'post', 'put', 'delete')
    ),
    array(
        'pattern' => '/baz',
        'controller' => 'Baz\Controller\BazController::bazAction',
        'method' => array('get', 'post', 'put', 'delete')
    )
);

$routingServiceProvider->addRoutes($app, $route);

```

### Registering providers with configuration

For this example the [ConfigServiceProvider](https://github.com/igorw/ConfigServiceProvider) is used to read the yml file. The ServiceRegisterProvider picks the stored configuration through the node `providers` in `$app['providers']`

`routes.yml`

```yml

config.routes:
  - pattern: '/foo'
    controller: 'Foo\Controller\FooController::fooAction'
    method: ['get', 'post']

```

`index.php`
```php

use Silex\Application;
use Igorw\Silex\ConfigServiceProvider;
use Marcojanssen\Provider\ServiceRegisterProvider;

//Set all routes
$app->register(
    new ConfigServiceProvider(__DIR__."/../app/config/routes.yml")
);

//Add all routes
$app->register(new RoutingServiceProvider);

```

## Todo

COnvert and before & after middleware still need to be implemented