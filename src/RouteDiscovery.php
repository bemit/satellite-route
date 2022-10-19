<?php

namespace Satellite\KernelRoute;

use Orbiter\AnnotationsUtil\AnnotationResult;
use Satellite\KernelRoute\Annotations\Delete;
use Satellite\KernelRoute\Annotations\Get;
use Satellite\KernelRoute\Annotations\Patch;
use Satellite\KernelRoute\Annotations\Post;
use Satellite\KernelRoute\Annotations\Put;
use Satellite\KernelRoute\Annotations\Route;

class RouteDiscovery {
    protected Router $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    /**
     * @param \Orbiter\AnnotationsUtil\AnnotationResult[] $routes
     * @return void
     */
    public function registerAnnotations(array $routes): void {
        foreach($routes as $route) {
            /**
             * @var AnnotationResult $route
             */
            if(!$route->getClass() || !$route->getAnnotation()) {
                continue;
            }
            /**
             * @var Route|Post|Get|Put|Delete|Patch $annotation
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
