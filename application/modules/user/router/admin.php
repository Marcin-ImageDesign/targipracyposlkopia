<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_user', new Zend_Controller_Router_Route(
    '/admin/user',
    [
        'module' => 'user',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('admin_user_new', new Zend_Controller_Router_Route(
    '/admin/user/new',
    [
        'module' => 'user',
        'controller' => 'admin',
        'action' => 'new',
    ]
));

$router->addRoute('admin_user_edit', new Zend_Controller_Router_Route(
    '/admin/user/edit/:hash',
    [
        'module' => 'user',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_user_delete', new Zend_Controller_Router_Route(
    '/admin/user/delete/:hash',
    [
        'module' => 'user',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_user_status', new Zend_Controller_Router_Route(
    '/admin/user/status/:hash',
    [
        'module' => 'user',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_user_new_simplified', new Zend_Controller_Router_Route(
    '/admin/user/new_simplified',
    [
        'module' => 'user',
        'controller' => 'admin',
        'action' => 'new-simplified',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
