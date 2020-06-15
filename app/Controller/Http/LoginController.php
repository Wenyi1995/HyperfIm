<?php
declare(strict_types=1);

namespace App\Controller\Http;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller()
 */
class LoginController extends AbstractController
{
    /**
     * @RequestMapping(path="login", methods="post")
     * @param RequestInterface $request
     * @return string
     */
    public function login(RequestInterface $request)
    {
        // 从请求中获得 id 参数
        $id = $request->input('id', 1);
        return (string)$id;
    }
}