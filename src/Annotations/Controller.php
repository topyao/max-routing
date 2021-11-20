<?php

namespace Max\Routing\Annotations;

use Max\Foundation\App;
use Max\Di\Annotations\Annotation;
use Max\Routing\RouteCollector;
use Max\Routing\Router;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller extends Annotation
{
    protected string $prefix = '';

    protected $middleware = [];

    public function __construct(...$args)
    {
        parent::__construct($args);
        $routeCollector = App::getInstance()->make(RouteCollector::class);
        App::getInstance()->set('route', new Router($routeCollector, $this->prefix, (array)$this->middleware));
    }

}
