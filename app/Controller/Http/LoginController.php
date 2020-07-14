<?php
declare(strict_types=1);

namespace App\Controller\Http;

use App\Model\User;
use App\Request\LoginRequest;
use App\Services\LoginTool;
use Hyperf\Database\Exception\QueryException;


class LoginController extends AbstractController
{
    /**
     * @param LoginRequest $request
     */
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $info = User::query()->where('name', $data['name'])->first();
        if (empty($info)) {
            return $this->response->withStatus(400)->withBody(__('user not exists'));
        } else {
            $pass = md5($data['pass'] . $info->salt);
            if ($pass == $info->pass) {
                return $this->response->withBody(LoginTool::instance()->tokenBuild($info->id, $info->name, $request->header('appId')));
            } else {
                return $this->response->withStatus(400)->withBody(__('wrong password'));
            }
        }

    }


    /**
     * @param LoginRequest $request
     * @return string
     */
    public function register(LoginRequest $request)
    {
        $data = $request->validated();
        try {
            $m = new User();
            $m->createUser($data);
            return 'success';
        } catch (QueryException $ex) {
            return $this->response->withStatus(422)->raw(__('user ready exists'));
        }
    }
}