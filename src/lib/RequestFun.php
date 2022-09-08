<?php
namespace Zhzy\Swow\lib;

/**
 * 为 swoole request 添加方法
 * Class RequestFun
 * @package Zhzy\Swow\lib
 */
class RequestFun
{
    protected $request;

    public function __construct(\Swoole\Http\Request $request)
    {
        $this->request = $request;
    }

    public function getMethod(): string
    {
        return $this->request->server['request_method'];
    }

    public function getUri(): string
    {
        return $this->request->server['request_uri'];
    }

}