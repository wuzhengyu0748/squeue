<?php

namespace SQueue\Component;

class ReadyQueue
{
    const READY_QUEUE_KEY_PREFIX = 'SQueue:ReadyQueue:topic-';

    /**
     * @param $driver
     * @param $topic
     * @param $jobId
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:26
     */
    public static function push($driver, $topic, $jobId)
    {
        $readyQueueKey = self::getReadyQueueKeyByTopic($topic);

        $driver->lpush($readyQueueKey, $jobId);
    }

    /**
     * @param $driver
     * @param $topic
     * @return mixed|null
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:26
     */
    public static function pop($driver, $topic)
    {
        $readyQueueKey = self::getReadyQueueKeyByTopic($topic);

        $jobId = $driver->rpop($readyQueueKey);
        // todo 延迟队列加入
        $jobBody = JobPool::getJob($driver, $jobId);

        return $jobBody ?? null;
    }

    /**
     * @param $topic
     * @return string
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:15
     */
    private static function getReadyQueueKeyByTopic($topic)
    {
        return self::READY_QUEUE_KEY_PREFIX . $topic;
    }
}