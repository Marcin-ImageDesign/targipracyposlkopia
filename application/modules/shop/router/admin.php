<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 21.10.13
 * Time: 13:02
 * To change this template use File | Settings | File Templates.
 */
$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('shop_admin', new Zend_Controller_Router_Route(
    '/admin/@shop',
    [
        'module' => 'shop',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('shop_admin_show', new Zend_Controller_Router_Route(
    '/admin/@shop/@show/:hash',
    [
        'module' => 'shop',
        'controller' => 'admin',
        'action' => 'show',
    ]
));

$router->addRoute('shop_admin_delete', new Zend_Controller_Router_Route(
    '/admin/@shop/delete/:hash',
    [
        'module' => 'shop',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('shop_admin_export-list', new Zend_Controller_Router_Route(
    '/admin/@shop/export/list',
    [
        'module' => 'shop',
        'controller' => 'admin',
        'action' => 'export-list',
    ]
));

$router->addRoute('shop_admin-location', new Zend_Controller_Router_Route(
    '/admin/@shop/@location',
    [
        'module' => 'shop',
        'controller' => 'admin-location',
        'action' => 'index',
    ]
));

$router->addRoute('shop_admin-location_new', new Zend_Controller_Router_Route(
    '/admin/@shop/@location/@new',
    [
        'module' => 'shop',
        'controller' => 'admin-location',
        'action' => 'new',
    ]
));

$router->addRoute('shop_admin-location_edit', new Zend_Controller_Router_Route(
    '/admin/@shop/@location/edit/:hash',
    [
        'module' => 'shop',
        'controller' => 'admin-location',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('shop_admin-location_delete', new Zend_Controller_Router_Route(
    '/admin/@shop/@location/@delete/:hash',
    [
        'module' => 'shop',
        'controller' => 'admin-location',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
