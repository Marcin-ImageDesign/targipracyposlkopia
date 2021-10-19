<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_event', new Zend_Controller_Router_Route(
    '/admin/event',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('admin_event_new', new Zend_Controller_Router_Route(
    '/admin/event/new',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'new',
    ]
));

$router->addRoute('admin_event_edit', new Zend_Controller_Router_Route(
    '/admin/event/edit/:hash',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event_delete', new Zend_Controller_Router_Route(
    '/admin/event/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event_status', new Zend_Controller_Router_Route(
    '/admin/event/status/:hash',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event_is-chat-schedule', new Zend_Controller_Router_Route(
    '/admin/event/@route_chat_schedule/:hash',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'is-chat-schedule',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event_archive', new Zend_Controller_Router_Route(
    '/admin/event/archive/:hash',
    [
        'module' => 'event',
        'controller' => 'admin',
        'action' => 'archive',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
