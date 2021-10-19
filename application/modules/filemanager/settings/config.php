<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['filemanager']['enabled'] = true;
$config['filemanager']['name'] = 'File manager';

$config['filemanager']['navigation']['admin'] = [
    [
        'label' => 'File manager',
        'route' => 'site_files',
        'resource' => 'filemanager',
        'pages' => [],
    ],
];

return $config;
