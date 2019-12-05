<?php

namespace Satellite\KernelRoute\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Put {
    /**
     * @var string url path
     */
    public $path;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string autofilled from discovery when applied to a method
     */
    public $handler;
    /**
     * @var string one http method like GET, PUT, DELETE, or use special annotations
     */
    public $method = 'PUT';
}
