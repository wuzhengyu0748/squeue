<?php

namespace SQueue\Component;

class Driver
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
        if (!(static::$_instance instanceof \Redis)) {
            static::$_instance = new \Redis();
            static::$_instance->connect($ip, $port);
            if ($auth) {
                static::$_instance->auth($auth);
            }
        }

        return static::$_instance;
    }
}