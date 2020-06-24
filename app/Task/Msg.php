<?php

namespace App\Task;

use App\Model\User;
use Hyperf\Di\Annotation\Inject;
use App\Services\PushData;

class Msg
{

    /**
     * @Inject
     * @var PushData
     */
    protected $pusher;

    public function whoIAm($fd, $data)
    {
        $uInfo = User::query()->find($data['uid']);
        $this->pusher->pushData(['type' => 'whoYouAre', 'fd' => $fd, 'uid' => $uInfo['id'], 'name' => $uInfo['name']], $fd);
    }
}