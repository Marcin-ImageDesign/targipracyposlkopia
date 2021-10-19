<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['brand']['enabled'] = true;
$config['brand']['name'] = 'Kalendarz';

$config['brand']['navigation']['admin'] = [
    [
        'label' => 'Brands',
        'route' => 'brand_admin',
        'resource' => 'brand_admin',
        'pages' => [],
    ],
];

return $config;
