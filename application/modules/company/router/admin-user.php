<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('company_admin-user', new Zend_Controller_Router_Route(
    '/admin/company/users-list/:hash',
    [
        'module' => 'company',
        'controller' => 'admin-user',
        'action' => 'index',
        'hash' => '',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin-user_new', new Zend_Controller_Router_Route(
    '/admin/company/user/new',
    [
        'module' => 'company',
        'controller' => 'admin-user',
        'action' => 'new',
    ]
));

$router->addRoute('company_admin-user_edit', new Zend_Controller_Router_Route(
    '/admin/company/user/edit/:hash',
    [
        'module' => 'company',
        'controller' => 'admin-user',
        'action' => 'edit',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin-user_delete', new Zend_Controller_Router_Route(
    '/admin/company/user/delete/:hash',
    [
        'module' => 'company',
        'controller' => 'admin-user',
        'action' => 'delete',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

$router->addRoute('company_admin-user_status', new Zend_Controller_Router_Route(
    '/admin/company/user/status/:hash',
    [
        'module' => 'company',
        'controller' => 'admin-user',
        'action' => 'status',
    ],
    [
        'hash' => '([a-z0-9]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
