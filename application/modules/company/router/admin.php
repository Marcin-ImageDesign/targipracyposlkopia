<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('company_admin', new Zend_Controller_Router_Route(
    '/admin/company',
    [
        'module' => 'company',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('company_admin_new', new Zend_Controller_Router_Route(
    '/admin/company/new',
    [
        'module' => 'company',
        'controller' => 'admin',
        'action' => 'new',
    ]
));

$router->addRoute('company_admin_edit', new Zend_Controller_Router_Route(
    '/admin/company/edit/:hash',
    [
        'module' => 'company',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin_delete', new Zend_Controller_Router_Route(
    '/admin/company/delete/:hash',
    [
        'module' => 'company',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin_status', new Zend_Controller_Router_Route(
    '/admin/company/status/:hash',
    [
        'module' => 'company',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin_img_delete', new Zend_Controller_Router_Route(
    '/admin/company/delete-img/:hash',
    [
        'module' => 'company',
        'controller' => 'admin',
        'action' => 'delete-img',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
