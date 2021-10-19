<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['translation']['enabled'] = true;
$config['translation']['name'] = 'Translations';

$config['translation']['navigation']['admin'] = [
    [
        'label' => 'Translations',
        'route' => 'admin_translation',
        'resource' => 'translation_admin_index',
    ],
];

return $config;
