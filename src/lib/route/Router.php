<?php
namespace Zhzy\Swow\lib\route;

use Zhzy\Swow\lib\RequestFun;
use Zhzy\Swow\lib\route\Route;

/**
 * 路由绑定 分发
 * Class Router
 * @package dZhzy\Swow\lib\route
 */
class Router
{
    const REQUEST_METHOD_GET = "GET";
    const REQUEST_METHOD_POST = "POST";

    private static $instance = null;

    private $routeCollection = [];

    private $MatchRouteCallable = null;

    private function __construct()
    {
    }

    public static function getInstance(): Router
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * add route to container
     * @param Route $route
     */
    private function addRouteToContainer(Route $route)
    {
        $this->routeCollection[] = $route;
    }

    /**
     * create  a new route
     * @param $method
     * @param $rule
     * @param $action
     * @return  Route
     */
    private static function newRoute($method, $rule, $action): \Zhzy\Swow\lib\route\Route
    {
        return new Route($method, $rule, $action);
    }

    /**
     * syntax sugar for get request
     * @param $rule string
     * @param $action string|array
     * @return  Route
     */
    public static function get($rule, $action): \Zhzy\Swow\lib\route\Route
    {
        $route = self::newRoute(self::REQUEST_METHOD_GET, $rule, $action);
        self::getInstance()
            ->addRouteToContainer($route);
        return $route;
    }

    /**
     * syntax sugar for post request
     * @param $rule string
     * @param $action string
     * @return  Route
     */
    public static function post($rule, $action): \Zhzy\Swow\lib\route\Route
    {
        $route = self::newRoute(self::REQUEST_METHOD_POST, $rule, $action);
        self::getInstance()
            ->addRouteToContainer($route);
        return $route;
    }

    public function getRouteCollection(): array
    {
        return $this->routeCollection;
    }

    /**
     * 分发任务
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @param $serv
     */
    public function dispatch(\Swoole\Http\Request $request,\Swoole\Http\Response $response,$serv)
    {
        $this->dispatchToRoute($request,$response,$serv);
    }

    /**
     * 执行任务
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @param $serv
     * @return mixed|string
     */
    public function dispatchToRoute(\Swoole\Http\Request $request,\Swoole\Http\Response $response,$serv)
    {
        return $this->runRoute($request, $response, $serv, $this->findRoute($request));
    }

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @param $serv
     * @param Route $route
     * @return mixed|string
     */
    public function runRoute(\Swoole\Http\Request $request,\Swoole\Http\Response $response, $serv, Route $route)
    {
        return $route->run($request, $response, $serv);
    }

    /**
     * @param \Swoole\Http\Request $request
     * @return mixed
     */
    protected function findRoute(\Swoole\Http\Request $request)
    {
        $MatchRouteCallable = $this->MatchRouteCallable;
        if ($MatchRouteCallable !== null) {
            return $MatchRouteCallable($request);
        }

        $routes = $this->getRouteCollection();

        $req = new RequestFun($request);

        foreach ($routes as $item) {
            if ($req->getMethod() === $item->getMethod()) {
                $uri = trim($req->getUri(),'/');
                $r_uri = trim($item->getRule(),'/');
                if ($uri === $r_uri) {
                    return $item;
                }
            }
        }

        throw new \RuntimeException('no route match for: ' . $req->getUri());
    }

    /**
     * 自定义 设置匹配路由 闭包方法
     * @param callable $call
     */
    public function setMatchRouteCallable(callable $call)
    {
        $this->MatchRouteCallable = $call;
    }
}