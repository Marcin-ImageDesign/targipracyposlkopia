<?php

defined('_SERVICE_ACCESS') or die('Access Denied');

return [
    'front' => [
        'lifetime' => 10 * 60, // 10 mninut,
        'automatic_serialization' => true,
    ],
    'back' => [
        'cache_dir' => APPLICATION_TMP . '/cache',
    ],
];
