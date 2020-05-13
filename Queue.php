<?php

namespace SQueue;

use SQueue\Component\Driver;
use SQueue\Component\JobPool;
use SQueue\Component\ReadyQueue;

class Queue
{
    private static $driver;

    public function __construct($redisIp, $redisPort, $password = false)
    {
        // 连接redis
        static::$driver = Driver::getInstance($redisIp, $redisPort, $password);
    }

    public function startManager()
    {

    }

    /**
     * @param $topic
     * @param $body
     * @param int $delay
     * @param int $ttr
     * @return string
     * @throws \Exception
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 3:34
     */
    public function add($topic, $body, $delay = 300, $ttr = 60)
    {
        return JobPool::addJob(static::$driver, $topic, $body, $delay, $ttr);
    }

    /**
     * @param string $jobId
     * @return null
     * @throws \Exception
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 3:45
     */
    public function delete(string $jobId)
    {
        return JobPool::delJob(static::$driver, $jobId);
    }

    public function pop($topic)
    {
        ReadyQueue::pop(static::$driver, $topic);
    }

    /**
     * @param string $jobId
     * @return string
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 3:49
     */
    public function finish(string $jobId)
    {
        try {
            JobPool::delJob(static::$driver, $jobId);
        } catch (\Exception $e) {
            exit('job finish failed');
        }

        return $jobId;
    }
}