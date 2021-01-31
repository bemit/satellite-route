<?php

namespace Satellite\KernelRoute;

use Psr\Log\LoggerInterface;

class RouteDiscovery {
    public const CONTAINER_ID = 'routes';
    protected Router $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function registerAnnotations(\Psr\Container\ContainerInterface $container, LoggerInterface $logger) {
        // automatic registering of routes discovered by annotations
        $routes = $container->get(self::CONTAINER_ID);
        if(!is_array($routes)) {
            $logger->error(__CLASS__ . ' routes in container entry `' . self::CONTAINER_ID . '` must be array');
            return;
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

            $this->router->addRoute($annotation->path, $annotation->method ?? 'GET', [$route->getClass(), $annotation->handler], $annotation->name);
        }
    }
}
