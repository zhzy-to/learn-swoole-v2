<?php

require __DIR__ . '/vendor/autoload.php';

// 路由定义目录
define("ROUTE_DIR", __DIR__ . '/router');
// 静态资源目录
define("STATIC_DIR", __DIR__ . '/public/static');


$server = Zhzy\Swow\Server\WsServer::getInstance("0.0.0.0", 9501);

// 覆盖默认事件
$server->setCallFunc([
    'Message' => [new App\Scoket\Events\DefaultEvent,'onMessage'],
    'Open' => [new App\Scoket\Events\DefaultEvent,'onOpen'],
]);

$server->run();