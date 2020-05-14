<?php

namespace SQueue;

use SQueue\Component\DelayBucket;
use SQueue\Component\Driver;
use SQueue\Component\JobPool;
use SQueue\Component\ReadyQueue;

class Queue
{
    private static $driver;

    public function __construct($redisIp, $redisPort, $password = false)
    {
        static::$driver = Driver::getInstance($redisIp, $redisPort, $password);
    }

    public function startManager()
    {
        //todo 多进程 Timmer
        $pool = new \Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

        $pool->on('workerStart', function (\Swoole\Process\Pool $pool, int $workerId) {
            echo "Worker#{$workerId} is started\n";
            while (true) {
                DelayBucket::scan(static::$driver);
            }
        });

        $pool->on('workerStop', function (\Swoole\Process\Pool $pool, int $workerId) {
            echo "Worker#{$workerId} is stop\n";
        });

        $pool->start();
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
    public function add($topic, $body, $delay = 0, $ttr = 60)
    {
        $jobId = JobPool::addJob(static::$driver, $topic, $body, $delay, $ttr);

        if ($delay) {
            DelayBucket::add(static::$driver, $jobId, $delay);
        } else {
            ReadyQueue::push(static::$driver, $topic, $jobId);
        }

        return $jobId;
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