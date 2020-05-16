# squeue

### 依赖
- swoole >= 4.4
- php >= 7.1

### 安装
```
composer require wuzhengyu0748/squeue
```

### 代码示例

- 启动服务
```
// 实例化Squeue对象，参数传入 redis的ip和端口号，第三个参数密码可选
$mq = new \Squeue\Squeue('127.0.0.1', 6379, $passwd);

// 调用start函数启动队列服务
$mq->start();

```
以上代码使用php-cli模式运行即可，建议配合 supervisor 监控和管理进程

- 添加一个消息任务
```
// 实例化对象
$mq = new \Squeue\Squeue('127.0.0.1', 6379);

// 事件类型
$topic = 'orderSubmit';

// 消息内容
$body = [
    'orderId' => '1001',
    'price' => '222'
];

// 加入对列 返回值为 jobID
$jobId = $mq->add($topic, $body);

```
以上代码的 add 方法有四个参数 可选的三个参数传入一个秒数，代表延迟多少秒后再执行。 第四个参数也是传入秒数，代表如果任务失败或超时延迟多久重试。

- 消费队列
```
// 实例化对象
$mq = new \Squeue\Squeue('127.0.0.1', 6379);

// 事件类型
$topic = 'orderSubmit';

while (true) {

    // 从队列取出该事件类型的一个消息
    $job = $mq->pop($topic);
    
    // 执行相应的业务逻辑。。。。
    
    // 任务执行完毕后 调用finish 告知 squeue 队列被正常消费，否则squeue将根据add设置的TTR（默认60秒）的时间间隔重试，直到消息被正常应答
    $mq->finish($job->jobId);
}

```

- 删除某个消息
```
// 实例化对象
$mq = new \Squeue\Squeue('127.0.0.1', 6379);

// 任务被删除后立即生效，即使已经在队列也会被删除
$mq->delete($jobId);

```
