<?php
declare(strict_types=1);

namespace App\Controller\WebSocket;

use App\Services\LoginTool;
use App\Services\RedisService;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;

/**
 * @SocketIONamespace("/im")
 */
class WebSocketController extends BaseNamespace
{
    /**
     * @Event("event")
     * @param Socket $socket
     * @param $data
     * @return string
     */
    public function onEvent(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $uInfo = LoginTool::instance()->tokenCheck($data['token'], $data['appId']);
        if ($uInfo) {
            $redis = RedisService::instance()->getCon();
            $redis->hSet(config('custom.wsRedisKeys.userSidKey'), (string)$uInfo[0], (string)$socket->getSid());
            $redis->hSet(config('custom.wsRedisKeys.sidUserKey'), (string)$socket->getSid(), (string)$socket->getSid());
            // 应答
            return 'Event Received: ' . $uInfo[1];
        } else {
            $socket->disconnect();
        }
    }

    /**
     * @Event("join-room")
     * @param string $data
     */
    public function onJoinRoom(Socket $socket, $data)
    {
        var_dump($data);
        // 将当前用户加入房间
        $socket->join($data);
        // 向房间内其他用户推送（不含当前用户）
        $socket->to($data)->emit('event', $socket->getSid() . "has joined {$data}");
        // 向房间内所有人广播（含当前用户）
        $this->emit('event', 'There are ' . count($socket->getAdapter()->clients($data)) . " players in {$data}");
    }

    /**
     * @Event("say")
     * @param string $data
     */
    public function onSay(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }
}