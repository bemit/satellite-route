<?php

namespace Satellite\KernelRoute;

use FastRoute;
use Satellite\Event;
use Satellite\SystemLaunchEvent;

class Router {
    /**
     * @var array $routes
     */
    protected static $routes;
    /**
     * @var array $route_groups
     */
    protected static $route_groups;

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

    /**
     * @param $id
     * @param $method
     * @param $route
     * @param $handler
     *
     * @return self
     */
    public static function addRoute($id, $method, $route, $handler) {
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

    public static function addGroup($id, $prefix, $handler) {
        static::$route_groups[$id] = [
            'prefix' => $prefix,
            'handler' => $handler,
        ];
    }

    public static function delete($route, $handler) {
        return static::buildRouteData('DELETE', $route, $handler);
    }

    public static function put($route, $handler) {
        return static::buildRouteData('PUT', $route, $handler);
    }

    public static function post($route, $handler) {
        return static::buildRouteData('POST', $route, $handler);
    }

    public static function get($route, $handler) {
        return static::buildRouteData('GET', $route, $handler);
    }

    /**
     * @return \FastRoute\Dispatcher
     */
    protected static function buildRouter() {
        return FastRoute\simpleDispatcher(static function(FastRoute\RouteCollector $r) {
            foreach(static::$routes as $id => $route) {
                $r->addRoute(...static::destructRouteData($route));
            }

            foreach(static::$route_groups as $id => $route_group) {
                static::buildRouteGroup($route_group, $r);
            }
        });
    }

    protected static function buildRouteGroup($route_group, FastRoute\RouteCollector $r) {
        $r->addGroup($route_group['prefix'], static function(FastRoute\RouteCollector $re) use ($route_group) {
            foreach($route_group['handler'] as $id => $route) {
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
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

        $creator = new \Nyholm\Psr7Server\ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        return $creator->fromGlobals();
    }
}
