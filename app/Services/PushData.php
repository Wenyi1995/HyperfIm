<?php

namespace App\Services;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

class PushData
{

    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 发消息
     * @param array $data
     * @param $to
     * @param int $isSingle
     * @param int $delay
     * @return bool
     */
    public function pushData(array $data, $to, int $delay = 0, int $isSingle = 1)
    {
        $info = [
            'data' => $data,
            'isSingle' => $isSingle,
            'to' => $to,
        ];
        return $this->driver->push(new \App\Job\PushData($info), $delay);
    }

    /**
     * 房间广播
     * @param array $data
     * @param $room
     * @param int $delay
     * @return bool
     */
    public function pushRoom(array $data, $room, int $delay = 0)
    {
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        $to = $redis->SMEMBERS(config('custom.wsRedisKeys.room_visitor_fd_key') . $room);
        return $this->pushData($data, $to, $delay, 0);
    }

    /**
     * 推送错误信息
     * @param $msg
     * @param $fd
     * @param string $type
     */
    public function pushDataError($msg, $fd, $type = 'error')
    {
        $this->pushData(['type' => $type, 'msg' => $msg], $fd);
    }

    /**
     * 成功返回
     * @param $fd
     * @return bool
     */
    public function pushSuccess($fd)
    {
        return $this->pushData(['type' => 'ok'], $fd);
    }
}