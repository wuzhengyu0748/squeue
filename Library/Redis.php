<?php

namespace SQueue\Library;

class Redis
{
    private static $_instance;

    private function __construct()
    {
    }

    /**
     * @param $ip
     * @param $port
     * @param $auth
     * @return \Redis
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 2:38
     */
    public static function getInstance($ip, $port, $auth)
    {
        \Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

        if (!(static::$_instance instanceof \Redis)) {
            static::$_instance = new \Redis();
            static::$_instance->connect($ip, $port);
            if ($auth) {
                static::$_instance->auth($auth);
            }
            static::$_instance->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        }

        return static::$_instance;
    }
}