<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-user', new Zend_Controller_Router_Route(
    '/admin/event/user-list/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-user',
        'action' => 'index',
        'hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-user_confirm', new Zend_Controller_Router_Route(
    '/admin/event/user-list/confirm/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-user',
        'action' => 'confirm',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-user_delete', new Zend_Controller_Router_Route(
    '/admin/event/user-list/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-user',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));
$router->addRoute(
    'event_admin-user_new',
    new Zend_Controller_Router_Route(
        '/admin/event/user/@participation/new',
        [
            'module' => 'event',
            'controller' => 'admin-user',
            'action' => 'new-event-has-user',
        ]
    )
);

$router->addRoute(
    'event_admin-user_edit',
    new Zend_Controller_Router_Route(
        '/admin/event/user/@participation/edit/:hash',
        [
            'module' => 'event',
            'controller' => 'admin-user',
            'action' => 'edit-event-has-user',
        ],
        [
            'hash' => '([0-9a-f]{32})',
        ]
    )
);

Zend_Controller_Front::getInstance()->setRouter($router);
