<?php

defined('_SERVICE_ACCESS') or die('Access Denied');

$configPath = ROOT_PATH . '/config.php';
$config = file_exists($configPath) ? include $configPath : null;

$options = [
    'save_path' => 'session',
    'use_only_cookies' => true,
    'remember_me_seconds' => 864000,
    'gc_maxlifetime' => 2 * 60 * 60, // 2 godziny
    'gc_probability' => 1,
    'gc_divisor' => 100,
];

if (isset($config['session_cookie_domain'])) {
    $options['cookie_domain'] = $config['session_cookie_domain'];
}

return [
    'options' => $options,
    'saveHandler' => [
        'class' => 'Engine_Session_SaveHandler_Doctrine',
        'params' => [
            'lifetime' => 2 * 60 * 60, // 2 godziny,
        ],
    ],
];
