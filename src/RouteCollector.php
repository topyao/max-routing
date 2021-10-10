<?php

namespace Max\Routing;

use Max\Routing\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

class RouteCollector
{
    /**
     * 未分组的全部路由
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * @var Url
     */
    protected Url $url;

    public function __construct()
    {
        $this->url = new Url();
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * 添加一个路由
     *
     * @param Route $route
     *
     * @return $this
     */
    public function add(Route $route): RouteCollector
    {
        foreach ($route->methods as $method) {
            $this->addWithMethod($method, $route);
        }
        return $this;
    }

    /**
     * 添加到分组后的路由中
     *
     * @param       $method
     * @param Route $route
     */
    public function addWithMethod($method, Route $route)
    {
        $this->routes[$method][] = $route;
    }

    /**
     * 直接替换路由
     *
     * @param array $routes
     *
     * @return $this
     */
    public function make(array $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * 全部
     *
     * @return array
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * 匹配
     *
     * @param ServerRequestInterface $request
     *
     * @return Route
     * @throws RouteNotFoundException
     */
    public function resolve(ServerRequestInterface $request): Route
    {
        $requestUri    = $request->getUri()->getPath();
        $requestMethod = $request->getMethod();
        if (!isset($this->routes[$requestMethod])) {
            throw new RouteNotFoundException('Method Not Allowed : ' . $requestMethod, 405);
        }
        foreach ($this->routes[$requestMethod] as $route) {
            /* @var Route $route */
            $uri = $route->uri;
            if ($uri === $requestUri || preg_match('#^' . $uri . '$#iU', $requestUri, $match)) {
                if (isset($match)) {
                    array_shift($match);
                    $route->routeParams = $match;
                }
                $route->destination = $this->parseDestination($route->destination);
                return $route;
            }
        }
        throw new RouteNotFoundException('Not Found', 404);
    }

    /**
     * 将字符串地址解析为callable
     *
     * @param $destination
     *
     * @return false|mixed|string[]
     */
    protected function parseDestination($destination)
    {
        if (is_string($destination)) {
            $destination = explode('@', $destination, 2);
            if (2 !== count($destination)) {
                throw new \InvalidArgumentException('路由参数不正确!');
            }
            return $destination;
        }
        return $destination;
    }

    /**
     * 使用别名生成url
     *
     * @param string $alias
     * @param array  $args
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function buildUrl(string $alias, array $args = [])
    {
        return $this->url->build($alias, $args);
    }

}
