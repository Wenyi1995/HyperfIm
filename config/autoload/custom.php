<?php

declare(strict_types=1);

return [
    'secret' => [
        env('APP_ID') => env('APP_SECRET_KEY'),//应用APP_ID
    ],
    'TOKEN_PREFIX' => 'TOKEN', // TOKEN保存前缀
    'SEPARATOR' => '::'  // 缓存分隔符
];
