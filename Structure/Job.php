<?php

namespace SQueue\Structure;

class Job
{
    private $topic;
    private $body;
    private $delay;
    private $ttr;
    private $jobId;

    public function setTopic($value)
    {
        $this->topic = $value;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setBody($value)
    {
        $this->body = $value;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setDelay($value)
    {
        $this->delay = $value;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function setTTR($value)
    {
        $this->ttr = $value;
    }

    public function getTTR()
    {
        return $this->ttr;
    }

    public function setJobId($topic)
    {
        $this->jobId = self::generateJobId($topic);
    }

    public function getJobId()
    {
        return $this->jobId;
    }

    public static function generateJobId($topic)
    {
        return $topic . '_' . md5(time() . mt_rand(1,1000000));
    }
}