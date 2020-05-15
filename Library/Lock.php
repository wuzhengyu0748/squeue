<?php

namespace SQueue\Library;

class Lock
{
    const LOCK_PREFIX = 'SQueue:Lock:Bucket:no-';

    /**
     * @param $driver
     * @param $bucketId
     * @param $expire
     * @return bool|mixed
     * @author wuzhengyu
     * @date 2020/5/15 0015 下午 3:02
     */
    public static function lock($driver, $bucketId, $expire)
    {
        $key = self::getLockKeyByBucketId($bucketId);
        $value = self::generateIdentification();

        if ($driver->set($key, $value, ["NX", "EX" => $expire])) {
            return $value;
        }

        return false;
    }

    /**
     * @param $driver
     * @param $bucketId
     * @param $identification
     * @author wuzhengyu
     * @date 2020/5/15 0015 下午 3:03
     */
    public static function unlock($driver, $bucketId, $identification)
    {
        $key = self::getLockKeyByBucketId($bucketId);

        $script = <<<LUA
if redis.call('get', KEYS[1]) == ARGV[1] then
    return redis.call('del', KEYS[1])
else
    return 0
end
LUA;
        $driver->evaluate($script, [$key, $identification], 1);
    }

    /**
     * @param $bucketId
     * @return string
     * @author wuzhengyu
     * @date 2020/5/14 0014 下午 4:17
     */
    private static function getLockKeyByBucketId($bucketId)
    {
        return self::LOCK_PREFIX . $bucketId;
    }

    /**
     * @return string
     * @author wuzhengyu
     * @date 2020/5/15 0015 下午 3:03
     */
    private static function generateIdentification()
    {
        return uniqid(php_uname("n") . "_", true);
    }
}