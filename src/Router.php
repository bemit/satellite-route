<?php

namespace Satellite\KernelRoute;

use FastRoute;

class Router {
    /**
     * @var array $routes
     */
    protected array $routes = [];
    /**
     * @var array $route_groups
     */
    protected array $route_groups = [];

    /**
     * @var string|null
     */
    protected ?string $cache;

    public function __construct(?string $cache) {
        $this->cache = $cache;
    }

    /**
     * @param $path
     * @param $method
     * @param callable $handler callable or DI resolvable
     * @param string $id
     *
     * @return self
     */
    public function addRoute(string $path, string $method, $handler, string $id = '') {
        if($id === '') {
            $this->routes[] = $this->buildRouteData($method, $path, $handler);
        } else {
            $this->routes[$id] = $this->buildRouteData($method, $path, $handler);
        }

        return $this;
    }

    protected function buildRouteData($method, $path, $handler) {
        return [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    protected function destructRouteData($route) {
        return [
            $route['method'],
            $route['path'],
            $route['handler'],
        ];
    }

    /**
     * @param string $id
     * @param string $prefix
     * @param array $routes
     */
    public function addGroup(string $id, string $prefix, array $routes) {
        $this->route_groups[$id] = [
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
    public function delete(string $route, $handler) {
        return $this->buildRouteData('DELETE', $route, $handler);
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public function put(string $route, $handler) {
        return $this->buildRouteData('PUT', $route, $handler);
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public function post(string $route, $handler) {
        return $this->buildRouteData('POST', $route, $handler);
    }

    /**
     * @param string $route
     * @param callable $handler callable or DI resolvable
     *
     * @return array
     */
    public function get(string $route, $handler) {
        return $this->buildRouteData('GET', $route, $handler);
    }

    /**
     * @param string $prefix
     * @param array $routes
     *
     * @return array
     */
    public function group(string $prefix, array $routes) {
        return [
            'prefix' => $prefix,
            'routes' => $routes,
        ];
    }

    /**
     * @return \FastRoute\Dispatcher
     */
    public function buildRouter(): \FastRoute\Dispatcher {
        $collection = function(FastRoute\RouteCollector $r) {
            foreach($this->routes as $id => $route) {
                $r->addRoute(...$this->destructRouteData($route));
            }

            foreach($this->route_groups as $id => $route_group) {
                $this->buildRouteGroup($route_group, $r);
            }
        };

        if($this->cache) {
            return FastRoute\cachedDispatcher($collection, [
                'cacheFile' => $this->cache, /* required */
            ]);
        }

        return FastRoute\simpleDispatcher($collection);
    }

    protected function buildRouteGroup($route_group, FastRoute\RouteCollector $r) {
        $r->addGroup($route_group['prefix'], function(FastRoute\RouteCollector $re) use ($route_group) {
            foreach($route_group['routes'] as $id => $route) {
                if(isset($route['method'])) {
                    // route
                    $re->addRoute(...$this->destructRouteData($route));
                } else if(isset($route['prefix'])) {
                    // group
                    $this->buildRouteGroup($route, $re);
                }
            }
        });
    }
}
