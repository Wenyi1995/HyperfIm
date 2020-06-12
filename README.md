# 介绍

就是个基于hyperf框架写的聊天室；反正没事慢慢写，有些新东西想试试

# 框架依赖

 - PHP >= 7.2
 - Swoole PHP extension >= 4.4，and Disabled `Short Name` 不能用short name
 - OpenSSL PHP extension
 - JSON PHP extension
 - PDO PHP extension （If you need to use MySQL Client）
 - Redis PHP extension （If you need to use Redis Client）
 - Protobuf PHP extension （If you need to use gRPC Server of Client）

# 用composer安装好

以下命令启动

 - `$ php bin/hyperf.php start`

默认开放`9501`端口
