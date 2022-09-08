<?php

namespace Zhzy\Swow\lib\redis;

/**
 * redis 工具
 * Class Predis
 * @package demo\lib\redis
 */
class Predis
{
    public $redis = "";

    private static $_instance = null;

    public static function getInstance(): Predis
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function __construct()
    {
        try {
            $this->redis = new \Redis();
            $conn = $this->redis->connect(
                "host.docker.internal",
                6379,
                5.0
            );

            if (!$conn) {
                throw new \RuntimeException("redis conn error");
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("redis conn error:" . $e->getMessage());
        }

    }

    public function set($key, $value, $timer = 0): bool
    {
        if (!$key) {
            return false;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }
        if ($timer) {
            return $this->redis->setex($key, $timer, $value);
        }
        return $this->redis->set($key, $value);
    }

    public function get($key = '')
    {
        return $this->redis->get($key);
    }

    // 添加ws fd 到redis 集合中
    public function sAdd($key = '', $value)
    {
        return $this->redis->sAdd($key, $value);
    }

    // 删除
    public function sRem($key = '', $value): int
    {
        return $this->redis->sRem($key, $value);
    }

    // 获取
    public function sMembers($key = ''): array
    {
        return $this->redis->sMembers($key);
    }

    /**
     * @param $name
     * @param array $args
     * @return false|void
     */
    public function __call($name, array $args)
    {
        if (count($args) !== 2) {
            return false;
        }

        $this->redis->$name($args[0], $args[1]);
    }
}
