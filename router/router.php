<?php
/**
 * 路由定义文件
 */

use \Swoole\Http\Request;
use \Swoole\Http\Response;
use \Swoole\WebSocket\Server;
use Zhzy\Swow\lib\route\Router;

Router::get('live/favicon.ico', static function (Request $request, Response $response, Server $serv) {
    $response->redirect('/favicon.ico', $http_code = 302);
});

// 测试
Router::get('baidu','App\Http\Controllers\Index@index');

Router::get('test', static function (Request $request, Response $response, Server $serv) {

    $response->header('Content-Type', 'application/json; charset=utf-8');
    $response->end(json_encode([
        'status' => 1,
        'message' => '哈哈哈哈哈哈哈哈哈哈',
        'data' => [],
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

});

// 后台推送ws消息
Router::get('admin_push', static function (Request $request,Response $response, Server $serv) {

    $members = \Zhzy\Swow\lib\redis\Predis::getInstance()->sMembers('live_game_key');

    $param = $request->get;
    $data = [
        'status' => 1,
        'content' => $param['content'] ?? '',
        'type' => $param['type'] ?? 1,
        'team_id' => $param['team_id'] ?? '',
    ];

    foreach ($members as $fd) {
        // 获取详情
        $info = $serv->connection_info($fd);

        if (isset($info['websocket_status']) && $info['websocket_status'] === 3) {
            if ($serv->exist($fd)) {
                echo "发送数据到:" . $fd . PHP_EOL;
                $serv->push($fd, json_encode($data, JSON_PRETTY_PRINT));
            }
        }
    }

    $response->header('Content-Type', 'application/json; charset=utf-8');
    $response->end(json_encode([
        'status' => 1,
        'message' => 'success',
        'data' => [],
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
});


// 聊天室推送ws消息
Router::post('chart_push', static function (Request $request,Response $response, Server $serv) {

    $param = $request->post;
    $members = \Zhzy\Swow\lib\redis\Predis::getInstance()->sMembers('live_game_key');
    $data = [
        'status' => 1,
        'client_name' => '',
        'content' => $param['content'] ?? '',
        'type' => $param['type'] ?? 2,
        'team_id' => $param['team_id'] ?? '',
    ];

    foreach ($members as $fd) {
        $info = $serv->connection_info($fd);

        if (isset($info['websocket_status']) && $info['websocket_status'] === 3) {
            if ($serv->exist($fd)) {
                echo "发送数据到:" . $fd . PHP_EOL;
                $serv->push($fd, json_encode($data, JSON_PRETTY_PRINT));
            }
        }
    }

    $response->header('Content-Type', 'application/json; charset=utf-8');
    $response->end(json_encode([
        'status' => 1,
        'message' => 'success',
        'data' => [],
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
});
