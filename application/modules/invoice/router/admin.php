<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('invoice_admin-index', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('invoice_admin-new', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/new/',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'new',
    ]
));

$router->addRoute('invoice_admin-details', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/details/:hash',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'details',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('invoice_admin-edit', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/edit/:hash',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('invoice_admin-delete', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/delete/:hash',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('invoice_admin-download', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/download/:hash',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'download',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('invoice_admin-delete-file', new Zend_Controller_Router_Route(
    '/@route_admin/invoice/delete-file/:hash',
    [
        'module' => 'invoice',
        'controller' => 'admin',
        'action' => 'delete-file',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
