<?php
declare(strict_types=1);

namespace App\Controller\Websocket;

use App\Services\Tool;
use Hyperf\Di\Annotation\Inject;
use App\Services\LoginTool;
use App\Services\PushData;
use App\Services\RedisService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\Utils\ApplicationContext;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject
     * @var PushData
     */
    protected $push;

    public function onMessage($server, Frame $frame): void
    {
        $data = json_decode($frame->data, true);
        if ($data) {
            $methodArr = Tool::instance()->getCallMethod($data['method']);
            if ($methodArr) {
                $args = [
                    $frame->fd,
                    [
                        'uid' => RedisService::instance()->getCon()->hGet(config('custom.wsRedisKeys.fdUserKey'), (string)$frame->fd)
                    ]
                ];
                $container = ApplicationContext::getContainer();
                $exec = $container->get(TaskExecutor::class);
                $exec->execute(new Task($methodArr, $args));
            }
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        if ($reactorId) {
            Tool::instance()->closeSocket($fd);
        }
    }

    public function onOpen($server, Request $request): void
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
            $server->disconnect($request->fd, 1007, 'bad request');
        }
    }
}
