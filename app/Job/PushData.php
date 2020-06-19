<?php

namespace App\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;

class PushData extends Job
{
    public $params;

    public function __construct($params)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->params = $params;
    }

    public function handle()
    {

        $container = ApplicationContext::getContainer();
        $sender = $container->get(Sender::class);
        if ($this->params['isSingle']) {
            if ($sender->check((int)$this->params['to'])) {
                $sender->push((int)$this->params['to'], json_encode($this->params['data']));
            }
        } else {
            foreach ($this->params['to'] as $fd) {
                if ($sender->check((int)$fd)) {
                    $sender->push((int)$fd, json_encode($this->params['data']));
                }
            }
        }
    }
}