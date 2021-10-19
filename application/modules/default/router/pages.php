<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('pages_index', new Zend_Controller_Router_Route(
    '/@route_pages/@route_pages_index',
    [
        'module' => 'default',
        'controller' => 'pages',
        'action' => 'index',
    ]
));

$router->addRoute('pages_stand-desc', new Zend_Controller_Router_Route(
    '/@route_pages/@route_pages_stand-desc',
    [
        'module' => 'default',
        'controller' => 'pages',
        'action' => 'stand-desc',
    ]
));

$router->addRoute('pages_sponsoring', new Zend_Controller_Router_Route(
    '/@route_pages/@route_pages_sponsoring',
    [
        'module' => 'default',
        'controller' => 'pages',
        'action' => 'sponsoring',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
