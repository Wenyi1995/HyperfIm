<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;

class User extends Model
{
    protected $table = 'user';
    protected $fillable = [];
    protected $casts = [];


    /**
     * 创建用户
     * @param $uinfo
     * @return $this
     */
    public function createUser($uinfo)
    {
        $salt = $this->createSalt();
        $this->name = $uinfo['name'];
        $this->pass = md5($uinfo['pass'] . $salt);
        $this->salt = $salt;
        !empty($uinfo['email']) && $this->email = $uinfo['email'];
        $this->save();
        return $this;
    }

    /**
     * 创建盐值
     * @return string
     */
    private function createSalt()
    {
        return substr((string)rand(1000, 9999) . md5((string)time()), rand(0, 31), 4);
    }
}