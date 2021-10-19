<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config = [];

$config['menu']['enabled'] = true;
$config['menu']['name'] = 'Menu';

$config['menu']['navigation']['admin'] = [
    [
        'label' => 'Menu',
        'route' => 'admin_menu',
        'resource' => 'menu_admin',
        'pages' => [
            [
                'label' => 'Add subpage',
                'route' => 'admin_menu_new',
                'resource' => 'menu_admin_new',
            ],
            [
                'label' => 'Edit page',
                'route' => 'admin_menu_edit',
                'resource' => 'menu_admin_edit',
                'visible' => false,
            ],
        ],
    ],

    [
        'label' => 'Page settings',
        'route' => 'admin_settings',
        'resource' => 'menu_settings',
    ],
];

return $config;
