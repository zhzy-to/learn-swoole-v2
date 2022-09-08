<?php
namespace Zhzy\Swow\lib\route;

use Zhzy\Swow\lib\Controller;
use Zhzy\Swow\lib\RequestFun;

/**
 * 路由实例
 * Class Route
 * @package demo\lib\route
 */
class Route
{

    private $rule;
    private $request_method;
    private $action;

    /**
     * Route constructor.
     * @param $method string
     * @param $rule string
     * @param $action callable|string
     */
    public function __construct($method, $rule, $action)
    {
        $this->rule = $rule;
        $this->request_method = $method;
        $this->action = $action;
        $this->parseRule();
    }

    private function parseRule()
    {

    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->request_method;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @return callable|string
     */
    public function getAction()
    {
        return $this->action;
    }


    public function run(\Swoole\Http\Request $request,\Swoole\Http\Response $response, $serv)
    {

        // 执行
        $action = $this->action;

        try {
            if ($action instanceof \Closure) {
                return $action($request,$response,$serv);
            }

            if (is_string($action)) {
                $action = explode('@',$action);
                if (is_array($action)) {
                    [$controller, $func] = $action;
                    return (new $controller)->$func($request,$response,$serv);
                }
            }

            throw new \RuntimeException('call route func error');

        } catch (\Exception $exception) {
            throw new \RuntimeException($exception->getMessage());
        }

    }
}