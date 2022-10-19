<?php

namespace Satellite\KernelRoute;

use FastRoute;
use FastRoute\Dispatcher;

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

    public function __construct(?string $cache = null) {
        $this->cache = $cache;
    }

    /**
     * @param string $path
     * @param string $method
     * @param callable|array $handler callable or DI resolvable
     * @param string $id
     *
     * @return self
     */
    public function addRoute(string $path, string $method, callable|array $handler, string $id = ''): static {
        if($id === '') {
            $this->routes[] = [$method, $path, $handler];
        } else {
            $this->routes[$id] = [$method, $path, $handler];
        }

        return $this;
    }

    /**
     * @param string $id
     * @param string $prefix
     * @param array $routes
     */
    public function addGroup(string $id, string $prefix, array $routes): void {
        $this->route_groups[$id] = [
            'prefix' => $prefix,
            'routes' => $routes,
        ];
    }

    /**
     * @param string $prefix
     * @param array $routes
     *
     * @return array
     */
    public function group(string $prefix, array $routes): array {
        return [
            'prefix' => $prefix,
            'routes' => $routes,
        ];
    }

    /**
     * @return Dispatcher
     */
    public function buildRouter(): Dispatcher {
        $collection = function(FastRoute\RouteCollector $r) {
            foreach($this->routes as $id => $route) {
                $r->addRoute(...$route);
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

    protected function buildRouteGroup($route_group, FastRoute\RouteCollector $r): void {
        $r->addGroup($route_group['prefix'], function(FastRoute\RouteCollector $re) use ($route_group) {
            foreach($route_group['routes'] as $id => $route) {
                if(isset($route['method'])) {
                    // route
                    $re->addRoute(...$route);
                } else if(isset($route['prefix'])) {
                    // group
                    $this->buildRouteGroup($route, $re);
                }
            }
        });
    }
}
