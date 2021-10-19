<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('news_admin', new Zend_Controller_Router_Route(
    '/admin/news',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('news_admin_new', new Zend_Controller_Router_Route(
    '/admin/news/new',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'new',
    ]
));

$router->addRoute('news_admin_edit', new Zend_Controller_Router_Route(
    '/admin/news/edit/:hash',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('news_admin_status', new Zend_Controller_Router_Route(
    '/admin/news/status/:hash',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('news_admin_delete', new Zend_Controller_Router_Route(
    '/admin/news/delete/:hash',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('news_admin_delete-image', new Zend_Controller_Router_Route(
    '/admin/news/delete-image/:hash',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'delete-image',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('news_admin_chat-schedule', new Zend_Controller_Router_Route(
    '/admin/news/@route_chat_schedule',
    [
        'module' => 'news',
        'controller' => 'admin',
        'action' => 'chat-schedule',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
