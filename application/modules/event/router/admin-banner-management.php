<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_banner_management', new Zend_Controller_Router_Route(
    '/admin/event/@banner_management/:group',
    [
        'module' => 'event',
        'controller' => 'admin-banner-management',
        'action' => 'index',
        'group' => '',
    ],
    [
        'group' => '(.*)',
    ]
));

$router->addRoute('admin_banner_management_get-banner', new Zend_Controller_Router_Route(
    '/admin/event/@banner_management/@route_get-banner/:group',
    [
        'module' => 'event',
        'controller' => 'admin-banner-management',
        'action' => 'get-banner',
    ],
    [
        'group' => '(.*)',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
