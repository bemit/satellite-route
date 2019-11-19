<?php

namespace Satellite\KernelRoute;

use Psr\EventDispatcher\StoppableEventInterface;
use Satellite\Event\StoppableEvent;

class RouteEvent implements StoppableEventInterface {
    use StoppableEvent;

    /**
     * @var \Psr\Http\Server\MiddlewareInterface
     */
    public $router;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    public $request;
}
