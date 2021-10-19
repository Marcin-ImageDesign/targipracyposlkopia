<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['statistics']['enabled'] = true;
$config['statistics']['name'] = 'statistics';

$config['statistics']['navigation']['admin'] = [
    [
        'label' => 'Statistics',
        'route' => 'statistics_admin_index',
        'resource' => 'statistics_admin_index',
    ],
];

return $config;
