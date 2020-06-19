<?php
declare(strict_types=1);

namespace App\Controller\Websocket;

use Hyperf\Di\Annotation\Inject;
use App\Services\LoginTool;
use App\Services\PushData;
use App\Services\RedisService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject
     * @var PushData
     */
    protected $push;

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {

        $token = $request->get['token'];
        $appId = $request->get['appId'];
        $uInfo = LoginTool::instance()->tokenCheck($token, $appId);
        if ($uInfo) {
            $redis = RedisService::instance()->getCon();
            $redis->hSet(config('custom.wsRedisKeys.userFdKey'), (string)$uInfo[0], (string)$request->fd);
            $redis->hSet(config('custom.wsRedisKeys.fdUserKey'), (string)$request->fd, (string)$uInfo[0]);
            $this->push->pushSuccess($request->fd);
        } else {
            $this->push->pushDataError('bad request', $request->fd);
            $server->disconnect($request->fd, 1007, 'bad request');
        }
    }
}
