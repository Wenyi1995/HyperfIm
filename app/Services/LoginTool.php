<?php

namespace App\Services;

use Hyperf\Utils\Traits\StaticInstance;

class LoginTool
{
    use StaticInstance;


    /**
     * token检查
     * @param $token
     * @param $appId
     * @return array
     */
    public function tokenCheck($token, $appId)
    {
        $info = $this->decodeToken($token, $appId);
        if ($info) {
            $uInfo = RedisService::instance()->getCon()->get($this->userTokenKey($appId, $info[1]));
            return $uInfo ? $info : [];
        } else {
            return $info;
        }
    }

    /**
     * token生成
     * @param $uid
     * @param $uname
     * @param $appId
     * @return bool
     */
    public function tokenBuild($uid, $uname, $appId)
    {
        $token = $this->generateToken($uid, $uname, $appId);
        RedisService::instance()->getCon()->set($this->userTokenKey($appId, $uname), $token);
        return $token;
    }

    /**
     * 生成用户token的key
     * @param $appId
     * @param $uname
     * @return string
     */
    private function userTokenKey($appId, $uname)
    {
        return sprintf('%s%s%s%s%s', config('custom.TOKEN_PREFIX'), config('custom.SEPARATOR'), $appId, config('custom.SEPARATOR'), $uname);
    }

    /**
     * 生成token内容
     * @param $uid
     * @param $uname
     * @param $appId
     * @return string
     */
    public function generateToken($uid, $uname, $appId)
    {
        $key = config('custom.secret')[$appId];
        return $this->authcode(sprintf("%s\t%s", $uid, $uname), 'ENCODE', $key);
    }

    /**
     * 解密token获取ID和手机号
     * @param $token
     * @param $appId
     * @return array
     */
    private function decodeToken($token, $appId)
    {
        $key = config('custom.secret')[$appId];
        $decode = $this->authcode($token, 'DECODE', $key);
        $arr = [];
        if ($decode) {
            $arr = explode("\t", $decode);
        }

        return $arr;
    }

    /**
     * DISCUZ的加解密函数
     * @param $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @param int $ckey_length
     * @return string
     */
    private function authCode($string, $operation = 'DECODE', $key = 'zsextdrcyftvugybhinjomk3284794503u64jife2fp9u2', $expiry = 0, $ckey_length = 0)
    {
        // 动态密钥长度
        $ckey_length = $ckey_length ? $ckey_length : 4;
        // 密钥
        $key = md5($key);
        // 密钥A用于加密
        $keya = md5(substr($key, 0, 16));
        // 密钥B用于验证
        $keyb = md5(substr($key, 16, 16));
        // 密钥C，生成动态密码部分
        // 解密的时候获取需要解密的字符串前面的$ckey_length长度字符串
        // 加密的时候，用当前时间戳的微妙数md5加密的最后$ckey_length长度字符串
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 用于运算的密钥
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey [$i] = ord($cryptkey [$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box [$i] + $rndkey [$i]) % 256;
            $tmp = $box [$i];
            $box [$i] = $box [$j];
            $box [$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box [$a]) % 256;
            $tmp = $box [$a];
            $box [$a] = $box [$j];
            $box [$j] = $tmp;
            $result .= chr(ord($string [$i]) ^ ($box [($box [$a] + $box [$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}