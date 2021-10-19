<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('brand_admin', new Zend_Controller_Router_Route(
    '/admin/brand',
    [
        'module' => 'brand',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('brand_admin_new', new Zend_Controller_Router_Route(
    '/admin/brand/new/:parent',
    [
        'module' => 'brand',
        'controller' => 'admin',
        'action' => 'new',
        'parent' => '',
    ],
    [
        'parent' => '\d+',
    ]
));

$router->addRoute('brand_admin_edit', new Zend_Controller_Router_Route(
    '/admin/brand/edit/:id',
    [
        'module' => 'brand',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'id' => '^(\d{1,})$',
    ]
));

$router->addRoute('brand_admin_delete', new Zend_Controller_Router_Route(
    '/admin/brand/delete/:id',
    [
        'module' => 'brand',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'id' => '^(\d{1,})$',
    ]
));

$router->addRoute('brand_admin_status', new Zend_Controller_Router_Route(
    '/admin/brand/state/:id',
    [
        'module' => 'brand',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'id' => '(\d{1,})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
