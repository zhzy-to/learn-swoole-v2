<?php

namespace App\Scoket\Events;

use Zhzy\Swow\lib\redis\Predis;

/**
 * 设置事件回调
 * Class DefaultEvent
 * @package App\Scoket\Events
 */
class DefaultEvent extends Base
{
    public function onMessage($ws, $frame)
    {
        echo "DefaultEvent Message: {$frame->data}\n";
        $ws->push($frame->fd, json_encode([
            'status' => 0,
            'msg' => 'DefaultEvent',
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
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
            'msg' => "DefaultEvent hello, welcome! you fd = {$request->fd} \n"
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}