<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_menu', new Zend_Controller_Router_Route(
    '/admin/menu',
    [
        'module' => 'menu',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('admin_menu_new', new Zend_Controller_Router_Route(
    '/admin/menu/new/:hash_parent',
    [
        'module' => 'menu',
        'controller' => 'admin',
        'action' => 'new',
        'hash_parent' => '',
    ],
    [
        'hash_parent' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_menu_edit', new Zend_Controller_Router_Route(
    '/admin/menu/edit/:hash',
    [
        'module' => 'menu',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_menu_delete', new Zend_Controller_Router_Route(
    '/admin/menu/delete/:hash',
    [
        'module' => 'menu',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_menu_status', new Zend_Controller_Router_Route(
    '/admin/menu/status/:hash',
    [
        'module' => 'menu',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_menu_move', new Zend_Controller_Router_Route_Regex(
    'admin/menu/move/(\d+)/(inside|before|after)/(\d+)',
    [
        'module' => 'menu',
        'controller' => 'admin',
        'action' => 'move',
    ],
    [
        1 => 'id_menu_move',
        2 => 'type',
        3 => 'id_menu',
    ],
    'admin/menu/move'
));

Zend_Controller_Front::getInstance()->setRouter($router);
