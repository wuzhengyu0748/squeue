<?php

namespace SQueue\Component;

use SQueue\Library\Config;
use SQueue\Library\Lock;

class DelayBucket
{
    const DELAY_BUCKET_KEY_PREFIX = 'SQueue:DelayBucket:no-';

    /**
     * @param $driver
     * @param $id
     * @param $delay
     * @throws \Exception
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:50
     */
    public static function add($driver, $id, $delay)
    {
        $bucketId = self::getBucketId();

        $delayBucketKey = self::getDelayBucketKeyByBucketId($bucketId);

        $score = time() + $delay;

        if (!$driver->zadd($delayBucketKey, $score ,$id)) {
            throw new \Exception("DelayBucket add failed");
        }
    }

    /**
     * @param $driver
     * @return array
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 5:22
     */
    public static function scan($driver)
    {
        $bucketId = self::getBucketId();

        $delayBucketKey = self::getDelayBucketKeyByBucketId($bucketId);

        $now = time();
        $beforeFiveMinutes = $now - 300;

        if ($identification = Lock::lock($driver, $bucketId, 1)) {

            \Swoole\Coroutine\System::sleep(0.01);

            $res = $driver->zrangebyscore($delayBucketKey, $beforeFiveMinutes, $now, ['withscores' => TRUE]);

            if($res) {
                foreach ($res as $jobId => $execTime) {
                    $topic = JobPool::getTopicByJobId($jobId);
                    ReadyQueue::push($driver, $topic, $jobId);
                }

                $driver->zremrangebyscore($delayBucketKey, $beforeFiveMinutes, $now);
            }

            Lock::unlock($driver, $bucketId, $identification);
        }
    }

    /**
     * @return mixed
     * @author wuzhengyu
     * @date 2020/5/14 0014 下午 4:23
     */
    private static function getBucketId()
    {
        $container = range(1, Config::BUCKET_NUM);

        shuffle($container);

        return $container[array_rand($container)];
    }

    /**
     * @param $bucketId
     * @return string
     * @author wuzhengyu
     * @date 2020/5/14 0014 下午 4:20
     */
    private static function getDelayBucketKeyByBucketId($bucketId)
    {
        return self::DELAY_BUCKET_KEY_PREFIX . $bucketId;
    }

}