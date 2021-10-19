<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['inquiry']['enabled'] = true;
$config['inquiry']['name'] = 'Inquiry';

$config['inquiry']['navigation']['admin'] = [
    [
        'label' => 'Inquiry',
        'route' => 'inquiry_admin',
        'resource' => 'inquiry_admin',
    ],
];

return $config;
