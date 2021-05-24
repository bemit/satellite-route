<?php

namespace Satellite\KernelRoute\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Delete {
    /**
     * @var string url path
     */
    public string $path;
    /**
     * @var string
     */
    public string $name = '';
    /**
     * @var string autofilled from discovery when applied to a method, defaults to `handle` for PSR `RequestHandlerInterface`
     */
    public string $handler = 'handle';
    /**
     * @var string one http method like GET, PUT, DELETE, or use special annotations
     */
    public string $method = 'DELETE';
}
