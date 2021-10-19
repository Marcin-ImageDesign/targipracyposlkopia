<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('user_account', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@account',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_edit', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/account/edit',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'edit',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_rank', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/account/rank',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'rank',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_send-to-friend', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/account/send-to-friend',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'send-to-friend',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_your-place', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/account/your-place',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'your-place',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_top-ten', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/account/top-ten',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'top-ten',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_day-ranking', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/account/day-ranking',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'day-ranking',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_change-pass', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@account/@change-pass',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'change-pass',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_account_change-email', new Zend_Controller_Router_Route(
    '/@change-email/:hash',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'change-email',
    ],
    [
        'hash' => '([0-9a-z]{32})',
    ]
));

$router->addRoute('user_account_notifications-history', new Zend_Controller_Router_Route(
    '/@notifications-history',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'notifications-history',
    ]
));

$router->addRoute('user_account_notification-delete', new Zend_Controller_Router_Route(
    '/@notification-delete/:hash',
    [
        'module' => 'user',
        'controller' => 'account',
        'action' => 'notification-delete',
    ],
    [
        'hash' => '([0-9a-z]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
