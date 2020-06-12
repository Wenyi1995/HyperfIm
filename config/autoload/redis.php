<?php

declare(strict_types=1);

$default = [
    'host' => env('REDIS_HOST', 'localhost'),
    'auth' => env('REDIS_AUTH', null),
    'port' => (int)env('REDIS_PORT', 6379),
    'pool' => [
        'min_connections' => 1,
        'max_connections' => 10,
        'connect_timeout' => 10.0,
        'wait_timeout' => 3.0,
        'heartbeat' => -1,
        'max_idle_time' => (float)env('REDIS_MAX_IDLE_TIME', 60),
    ],
];
$configReturn = [];
for ($i = 0; $i <= 15; $i++) {
    $configReturn['db_' . (string)$i] = array_merge($default, ['db' => $i]);
}
$configReturn['default'] = $configReturn['db_0'];
return $configReturn;
