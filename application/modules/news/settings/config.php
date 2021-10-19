<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['news']['enabled'] = true;
$config['news']['name'] = 'News';

$config['news']['navigation']['admin'] = [
    [
        'label' => 'News',
        'route' => 'news_admin',
        'resource' => 'news_admin',
        'pages' => [
            [
                'label' => 'Add news',
                'route' => 'news_admin_new',
                'resource' => 'news_admin_new',
                'visible' => false,
            ],
            [
                'label' => 'Edit news',
                'route' => 'news_admin_edit',
                'resource' => 'news_admin_edit',
                'visible' => false,
            ],
            [
                'label' => 'Chat schedule',
                'route' => 'news_admin_chat-schedule',
                'resource' => 'news_admin_chat-schedule',
            ],
        ],
    ],
];

return $config;
