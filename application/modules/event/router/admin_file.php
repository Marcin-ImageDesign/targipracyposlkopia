<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-file', new Zend_Controller_Router_Route(
    '/admin/event/file/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-file',
        'action' => 'index',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-file_new', new Zend_Controller_Router_Route(
    '/admin/event/file/new/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-file',
        'action' => 'new',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-file_edit', new Zend_Controller_Router_Route(
    '/admin/event/file/edit/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-file',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-file_delete', new Zend_Controller_Router_Route(
    '/admin/event/file/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-file',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-file_mail-text', new Zend_Controller_Router_Route(
    '/admin/event/file/mail-text/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-file',
        'action' => 'mail-text',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
