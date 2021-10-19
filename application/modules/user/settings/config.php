<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['user']['enabled'] = true;
$config['user']['name'] = 'User';

$config['user']['navigation']['admin'] = [
    [
        'label' => 'Users',
        'route' => 'admin_user',
        'resource' => 'user_admin',
        'pages' => [
            [
                'label' => 'Edit user',
                'route' => 'admin_user_edit',
                'resource' => 'user_admin_edit',
                'visible' => false,
            ],
        ],
    ],
];

$config['user']['navigation']['default'] = [
    'label' => 'Account',
    'route' => 'user_account',
    'visible' => false,
    'pages' => [
        [
            'label' => 'Edit account',
            'route' => 'user_account_edit',
            'visible' => true,
        ],
        [
            'label' => 'Change password',
            'route' => 'user_account_change-pass',
            'visible' => true,
        ],
        [
            'label' => 'Logout',
            'route' => 'logout',
            'visible' => true,
        ],
    ],
];

return $config;
