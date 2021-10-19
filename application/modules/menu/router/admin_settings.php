<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_settings-seo', new Zend_Controller_Router_Route(
    '/admin/settings/seo',
    [
        'module' => 'menu',
        'controller' => 'settings',
        'action' => 'index',
    ]
));

$router->addRoute('admin_settings-site', new Zend_Controller_Router_Route(
    '/admin/settings/site',
    [
        'module' => 'menu',
        'controller' => 'settings',
        'action' => 'index',
    ]
));

$router->addRoute('admin_settings_variable', new Zend_Controller_Router_Route(
    '/admin/settings/variable',
    [
        'module' => 'menu',
        'controller' => 'settings',
        'action' => 'variable',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
