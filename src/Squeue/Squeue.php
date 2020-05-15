<?php

namespace Squeue;

use Squeue\Component\DelayBucket;
use Squeue\Library\Config;
use Squeue\Library\Redis;
use Squeue\Component\JobPool;
use Squeue\Component\ReadyQueue;

class Squeue
{
    private static $driver;

    public function __construct($redisIp, $redisPort, $password = false)
    {
        static::$driver = Redis::getInstance($redisIp, $redisPort, $password);
    }

    public function start()
    {
        $process = new \Swoole\Process(function () {

            echo "Squeue is started\n";

            while (true) {
                DelayBucket::scan(static::$driver);
            }

        }, false, 0, true);

        $process->start();

        \Swoole\Process::wait(true);

        echo "Squeue exit" . PHP_EOL;

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

    /**
     * @param $topic
     * @return mixed|null
     * @throws \Exception
     * @author wuzhengyu
     * @date 2020/5/15 0015 下午 5:51
     */
    public function pop($topic)
    {
        return ReadyQueue::pop(static::$driver, $topic);
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

    /**
     * @param array $params
     * @author wuzhengyu
     * @date 2020/5/15 0015 下午 3:04
     */
    public function set(array $params)
    {
        foreach ($params as $key => $val) {
            if (isset(Config::$$key)) {
                Config::$$key = $val;
            }
        }
    }
}