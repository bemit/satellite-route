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
            /**
             * @var \Orbiter\AnnotationsUtil\AnnotationResult $route
             */
            if(!$route->getClass() || !$route->getAnnotation()) {
                continue;
            }
            /**
             * @var \Satellite\KernelRoute\Annotations\Route|\Satellite\KernelRoute\Annotations\Post|\Satellite\KernelRoute\Annotations\Get|\Satellite\KernelRoute\Annotations\Put|\Satellite\KernelRoute\Annotations\Delete $annotation
             */
            $annotation = $route->getAnnotation();
            if($route->getMethod()) {
                // If the annotation was targeted at an method, set the method as handler
                $annotation->handler = $route->getMethod();
            }

            Router::addRoute($annotation->path, $annotation->method ?? 'GET', [$route->getClass(), $annotation->handler], $annotation->name);
        }

        return $exec;
    }
}
