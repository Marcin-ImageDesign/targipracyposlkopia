<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-site', new Zend_Controller_Router_Route(
    '/admin/event/:hash/site',
    [
        'module' => 'event',
        'controller' => 'admin-site',
        'action' => 'index',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-site_new', new Zend_Controller_Router_Route(
    '/admin/event/:hash/site/new',
    [
        'module' => 'event',
        'controller' => 'admin-site',
        'action' => 'new',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-site_edit', new Zend_Controller_Router_Route(
    '/admin/event/site/edit/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-site',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-site_delete', new Zend_Controller_Router_Route(
    '/admin/event/site/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-site',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-site_status', new Zend_Controller_Router_Route(
    '/admin/event/site/status/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-site',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
