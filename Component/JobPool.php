<?php

namespace SQueue\Component;

use SQueue\Structure\Job;

class JobPool
{

    const JOB_POOL_KEY_PREFIX = 'SQueue:JobPool:topic-';

    /**
     * @param $driver
     * @param $topic
     * @param $body
     * @param $delay
     * @param $ttr
     * @return string
     * @throws \Exception
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 3:17
     */
    public static function addJob($driver, $topic, $body, $delay, $ttr)
    {
        $jobPoolKey = self::getJobPoolKeyByTopic($topic);

        $job = new Job();
        $job->setJobId($topic);
        $job->setTopic($topic);
        $job->setBody($body);
        $job->setDelay($delay);
        $job->setTTR($ttr);

        $jobId = $job->getJobId();
        $serializeJob = serialize($job);

        if (!$driver->hSet($jobPoolKey, $jobId, $serializeJob)) {
            throw new \Exception("job add failed");
        }

        return $jobId;
    }

    /**
     * @param $driver
     * @param $id
     * @return mixed|null
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:22
     */
    public static function getJob($driver, $id)
    {
        $topic = self::getTopicByJobId($id);

        $jobPoolKey = self::getJobPoolKeyByTopic($topic);

        $job = $driver->hGet($jobPoolKey, $id);

        return $job ? unserialize($job) : null;
    }

    /**
     * @param $driver
     * @param $id
     * @return null
     * @throws \Exception
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 3:44
     */
    public static function delJob($driver, $id)
    {
        $topic = self::getTopicByJobId($id);

        $jobPoolKey = self::getJobPoolKeyByTopic($topic);

        if (!$driver->hDel($jobPoolKey, $id)) {
            throw new \Exception("job delete failed");
        }

        return $id;
    }

    /**
     * @param $id
     * @return mixed|null
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 4:23
     */
    public static function getTopicByJobId($id)
    {
        if (strpos($id, '_') === false || strlen($id) < 3) {
            return null;
        }

        $topic = explode('_', $id)[0];

        return $topic ?? null;
    }

    /**
     * @param $topic
     * @return string
     * @author wuzhengyu
     * @date 2020/5/13 0013 下午 3:45
     */
    private static function getJobPoolKeyByTopic($topic)
    {
        return self::JOB_POOL_KEY_PREFIX . $topic;
    }



}