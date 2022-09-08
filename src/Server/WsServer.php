<?php

namespace Zhzy\Swow\Server;

use http\Exception\RuntimeException;
use Zhzy\Swow\lib\redis\Predis;
use Zhzy\Swow\lib\route\Router;

/**
 * ws 服务 包含http
 * Class WsServer
 * @package Zhzy\Swow\Server
 */
class WsServer
{
    /**
     * 如果改成protected 有问题
     * @var \Swoole\WebSocket\Server
     */
    public $ws;

    /**
     * @var
     */
    public $host;

    /**
     * @var
     */
    public $port;

    /**
     * 事件方法
     * @var array|array[]
     */
    public array $callFunc = [];

    private function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ws = new \Swoole\WebSocket\Server($host, $port);

        //设置异步任务的工作进程数量
        $this->ws->set([
            'enable_static_handler' => true,
            'heartbeat_idle_time' => 600,
            'heartbeat_check_interval' => 60,
            'worker_num' => 4,
            //'task_worker_num' => 4,
            'document_root' => STATIC_DIR,
            'log_level' => 0,
            'log_file' => STATIC_DIR . '/sw.log'
        ]);
    }

    /**
     * 实例
     * @var
     */
    private static $instance;

    public static function getInstance(string $host,int $port): WsServer
    {
        if (!self::$instance) {
            self::$instance = new self($host, $port);
        }

        return self::$instance;
    }

    /**
     * 启动
     */
    public function run(): void
    {
        if (empty($this->callFunc)) {
            throw new RuntimeException('Server CallFunc is empty');
        }

        foreach ($this->callFunc as $on => $item) {
            if (!is_array($item)) {
                continue;
            }
            $this->ws->on($on,[...$item]);
        }

        $this->ws->start();
    }

    /**
     * 覆盖默认事件
     * @param array $arg
     */
    public function setCallFunc(array $arg = []): void
    {
        $this->callFunc = [
            'Open' => [$this, 'onOpen'],
            'Request' => [$this, 'onRequest'],
            'WorkerStart' => [$this, 'onWorkerStart'],
            'Shutdown' => [$this, 'onShutdown'],
            'WorkerExit' => [$this, 'onWorkerExit'],
            'Message' => [$this, 'onMessage'],
            'Close' => [$this, 'onClose'],
        ];
        $this->callFunc = array_merge($this->callFunc,$arg);
    }

    public function onShutdown(\swoole_server $server)
    {
        echo "服务结束" . PHP_EOL;
    }

    public function onWorkerExit(\swoole_server $server, int $worker_id)
    {
        echo "进程结束" . PHP_EOL;
    }

    public function onWorkerStart(\swoole_server $server, int $worker_id)
    {
        // 非任务进程
        if (!$server->taskworker) {
            echo "worker start" . PHP_EOL;

            // 加载路由文件
            $handle = opendir(ROUTE_DIR);
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    require ROUTE_DIR . '/' . $file;
                }
            }
        }
    }

    /**
     * 当ws链接
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request)
    {
        // 存入集合
        Predis::getInstance()->sAdd(
            "live_game_key",
            $request->fd
        );

        echo "client connect: {$request->fd}\n";

        $ws->push($request->fd, json_encode([
            'status' => 0,
            'msg' => "hello, welcome! you fd = {$request->fd} \n"
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    /**
     * http 请求
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response)
    {
        echo "request : {$request->server['request_uri']} \n";
        // 路由分发
        try {
            Router::getInstance()->dispatch($request,$response,$this->ws);

        } catch (\Exception $exception) {
            $response->header('Content-Type', 'text/html; charset=utf-8');
            $response->end("<h1> {$exception->getMessage()} </h1>");
        }

    }

    /**
     * 收到ws消息
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame)
    {
        echo "Message: {$frame->data}\n";
        $ws->push($frame->fd, json_encode([
            'status' => 0,
            'msg' => $frame->data
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    /**
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd)
    {
        Predis::getInstance()->sRem(
            "live_game_key",
            $fd
        );
        echo "client-{$fd} is closed\n";
    }
}