<?php

namespace App\Services;

use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Traits\StaticInstance;

class RedisService
{
    use StaticInstance;

    /**
     * 获取redis连接
     * @param int $db
     * @return object
     */
    public function getCon($db = 0)
    {
        $container = ApplicationContext::getContainer();
        return $container->get(RedisFactory::class)->get('db_' . (string)$db);
    }
}