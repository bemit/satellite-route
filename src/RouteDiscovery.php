<?php

namespace Satellite\KernelRoute;

use Satellite\SystemLaunchEvent;

class RouteDiscovery {

    public $container_id = 'routes';

    public function registerAnnotations(SystemLaunchEvent $exec, \Psr\Container\ContainerInterface $container) {
        // automatic registering of routes discovered by annotations
        if($exec->cli) {
            return $exec;
        }

        $routes = $container->get($this->container_id);
        if(!is_array($routes)) {
            return $exec;
        }

        foreach($routes as $route) {
            if(!isset($route['class'], $route['annotation'])) {
                continue;
            }
            /**
             * @var \Satellite\KernelRoute\Annotations\Route $annotation
             */
            $annotation = $route['annotation'];
            if(isset($route['method'])) {
                // If the annotation was targeted at an method, set the method as handler
                $annotation->handler = $route['method'];
            }

            Router::addRoute($annotation->name, $annotation->method ?? 'GET', $annotation->path, [$route['class'], $annotation->handler]);
        }

        return $exec;
    }
}
