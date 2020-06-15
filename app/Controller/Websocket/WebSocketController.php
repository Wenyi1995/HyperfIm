<?php
declare(strict_types=1);

namespace App\Controller\Websocket;

use App\Services\LoginTool;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
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
        $uInfo = LoginTool::instance()->tokenCheck($request->get['token'],'APP_ID');
        if($uInfo){
            $server->push($request->fd, LoginTool::instance()->tokenBuild('1','uname','APP_ID'));
        }else{
            $server->disconnect($request->fd,1006,'bad login');
        }
    }
}