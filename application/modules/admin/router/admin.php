<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin', new Zend_Controller_Router_Route(
    '/admin',
    [
        'module' => 'admin',
        'controller' => 'index',
        'action' => 'index',
    ]
));

$router->addRoute('admin_select-event', new Zend_Controller_Router_Route(
    '/admin/select-event',
    [
        'module' => 'admin',
        'controller' => 'index',
        'action' => 'select-event',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
