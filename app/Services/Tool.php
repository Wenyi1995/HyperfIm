<?php


namespace App\Services;

use Hyperf\Utils\Traits\StaticInstance;
use App\Services\RedisService;


class Tool
{
    use StaticInstance;

    /**
     * fd断开过后清理工作
     * @param $fd
     */
    public function closeSocket($fd)
    {
        $redis = RedisService::instance()->getCon();
        $uid = $redis->hGet(config('custom.wsRedisKeys.fdUserKey'), (string)$fd);
        $redis->hdel(config('custom.wsRedisKeys.fdUserKey'), (string)$fd);
        $redis->hDel(config('custom.wsRedisKeys.userFdKey'), (string)$uid);
    }


    /**
     * 获取客户端调用
     * @param $method
     * @return array
     */
    public function getCallMethod($method)
    {
        $methods = [
            'whoIAm' => [\App\Task\Msg::class, 'whoIAm'],
        ];
        if (isset($methods[$method])) {
            return $methods[$method];
        } else {
            return [];
        }
    }

}