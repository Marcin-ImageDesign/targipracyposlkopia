<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('text_admin', new Zend_Controller_Router_Route(
    '/admin/text',
    [
        'module' => 'text',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('text_admin_new', new Zend_Controller_Router_Route(
    '/admin/text/new/:hash',
    [
        'module' => 'text',
        'controller' => 'admin',
        'action' => 'new',
        'hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('text_admin_edit', new Zend_Controller_Router_Route(
    '/admin/text/edit/:hash',
    [
        'module' => 'text',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('text_admin_status', new Zend_Controller_Router_Route(
    '/admin/text/status/:hash',
    [
        'module' => 'text',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('text_admin_delete', new Zend_Controller_Router_Route(
    '/admin/text/delete/:hash',
    [
        'module' => 'text',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
