<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_settings-main', new Zend_Controller_Router_Route(
    '/admin/settings/main_page',
    [
        'module' => 'admin',
        'controller' => 'settings',
        'action' => 'main-page',
    ]
));

$router->addRoute('admin_settings-main-boxes', new Zend_Controller_Router_Route(
    '/admin/settings/main_page_boxes',
    [
        'module' => 'admin',
        'controller' => 'settings',
        'action' => 'main-page-boxes',
    ]
));

$router->addRoute('admin_settings_variable', new Zend_Controller_Router_Route(
    '/admin/settings/variable',
    [
        'module' => 'admin',
        'controller' => 'settings',
        'action' => 'variable',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
