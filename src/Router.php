<?php

namespace Satellite\KernelRoute;

use FastRoute;
use Satellite\Event;
use Satellite\SystemLaunchEvent;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class Router {
    /**
     * @var array $routes
     */
    protected static $routes = [];
    /**
     * @var array $route_groups
     */
    protected static $route_groups = [];

    /**
     * @var string|null
     */
    protected static $cache;

    /**
     * @param \Satellite\SystemLaunchEvent $exec
     *
     * @return \Satellite\SystemLaunchEvent
     */
    public static function handle(SystemLaunchEvent $exec) {
        if($exec->cli) {
            return $exec;
        }

        $response = new RouteEvent();
        $response->router = new \Middlewares\FastRoute(static::buildRouter());
        $response->request = static::createContext();

        Event::dispatch($response);

        return $exec;
    }

    public static function setCache($cache) {
        static::$cache = $cache;
    }

    /**
     * @param $id
     * @param $method
     * @param $route
     * @param callable $handler callable or DI resolvable
     *
     * @return self
     */
    public static function addRoute(string $id, string $method, string $route, $handler) {
        static::$routes[$id] = static::buildRouteData($method, $route, $handler);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return static::class;
    }

    protected static function buildRouteData($method, $route, $handler) {
        return [
            'method' => $method,
            'route' => $route,
            'handler' => $handler,
        ];
    }

    protected static function destructRouteData($route) {
        return [
            $route['method'],
            $route['route'],
            $route['handler'],
        ];
    }

    /**
     * @param string $id
     * @param string $prefix
     * @param array $routes
     */
    public static function addGroup(string $id, string $prefix, array $routes) {
        static::$route_groups[$id] = [
            'prefix' => $prefix,
            'routes' => $routes,
        ];
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public static function delete(string $route, $handler) {
        return static::buildRouteData('DELETE', $route, $handler);
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public static function put(string $route, $handler) {
        return static::buildRouteData('PUT', $route, $handler);
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public static function post(string $route, $handler) {
        return static::buildRouteData('POST', $route, $handler);
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public static function get(string $route, $handler) {
        return static::buildRouteData('GET', $route, $handler);
    }

    /**
     * @param string $prefix
     * @param array $routes
     *
     * @return array
     */
    public static function group(string $prefix, array $routes) {
        return [
            'prefix' => $prefix,
            'routes' => $routes,
        ];
    }

    /**
     * @return \FastRoute\Dispatcher
     */
    protected static function buildRouter() {
        $collection = static function(FastRoute\RouteCollector $r) {
            foreach(static::$routes as $id => $route) {
                $r->addRoute(...static::destructRouteData($route));
            }

            foreach(static::$route_groups as $id => $route_group) {
                static::buildRouteGroup($route_group, $r);
            }
        };

        if((bool)static::$cache) {
            return FastRoute\cachedDispatcher($collection, [
                'cacheFile' => static::$cache, /* required */
            ]);
        }

        return FastRoute\simpleDispatcher($collection);
    }

    protected static function buildRouteGroup($route_group, FastRoute\RouteCollector $r) {
        $r->addGroup($route_group['prefix'], static function(FastRoute\RouteCollector $re) use ($route_group) {
            foreach($route_group['routes'] as $id => $route) {
                if(isset($route['method'])) {
                    // route
                    $re->addRoute(...static::destructRouteData($route));
                } else if(isset($route['prefix'])) {
                    // group
                    static::buildRouteGroup($route, $re);
                }
            }
        });
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected static function createContext() {
        $psr17Factory = new Psr17Factory();

        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        return $creator->fromGlobals();
    }
}
