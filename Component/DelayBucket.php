<?php

namespace SQueue\Component;

class DelayBucket
{
    const DELAY_BUCKET_KEY_PREFIX = 'SQueue:DelayBucket:no-1'; //todo 多个bucket

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
        $delayBucketKey = self::getDelayBucketKey();

        $score = time() + $delay;

        if (!$driver->zAdd($delayBucketKey, $score ,$id)) {
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
        $delayBucketKey = self::getDelayBucketKey();
        $now = time();
        $beforeFiveMinutes = $now - 300;

        $res = $driver->zrangebyscore($delayBucketKey, $beforeFiveMinutes, $now, ['withscores' => TRUE]);

        $now -= 1;
        $driver->zremrangebyscore($delayBucketKey, $beforeFiveMinutes, $now);

        return $res ?? [];
    }

    /**
     * @return string
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:59
     */
    private static function getDelayBucketKey()
    {
        return self::DELAY_BUCKET_KEY_PREFIX ;
    }

}