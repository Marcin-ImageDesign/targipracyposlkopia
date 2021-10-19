<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['invoice']['enabled'] = true;
$config['invoice']['name'] = 'invoice';

$config['invoice']['navigation']['admin'] = [
    [
        'label' => 'Invoice',
        'route' => 'invoice_admin_index',
        'resource' => 'invoice_admin_index',
    ],
];

return $config;
