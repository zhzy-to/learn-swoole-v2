<?php

namespace App\Http\Controllers;

use Zhzy\Swow\lib\Controller;
use \Swoole\Http\Request;
use \Swoole\Http\Response;
use \Swoole\WebSocket\Server;

/**
 * 控制器
 * Class Index
 * @package App\Http\Controllers
 */
class Index extends Controller
{
    public function index(Request $request, Response $response, Server $serv)
    {
        $response->redirect('https://www.baidu.com', $http_code = 302);
    }
}