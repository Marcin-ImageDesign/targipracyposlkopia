<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('company_admin-brand', new Zend_Controller_Router_Route(
    '/admin/company/brand-list',
    [
        'module' => 'company',
        'controller' => 'admin-brand',
        'action' => 'index',
    ]
));

$router->addRoute('company_admin-brand_new', new Zend_Controller_Router_Route(
    '/admin/company/brand/new',
    [
        'module' => 'company',
        'controller' => 'admin-brand',
        'action' => 'new',
    ]
));

$router->addRoute('company_admin-brand_edit', new Zend_Controller_Router_Route(
    '/admin/company/brand/edit/:hash',
    [
        'module' => 'company',
        'controller' => 'admin-brand',
        'action' => 'edit',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin-brand_delete', new Zend_Controller_Router_Route(
    '/admin/company/brand/delete/:hash',
    [
        'module' => 'company',
        'controller' => 'admin-brand',
        'action' => 'delete',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
