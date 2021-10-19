<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('home', new Zend_Controller_Router_Route(
    '/',
    [
        'module' => 'default',
        'controller' => 'index',
        'action' => 'index',
    ]
));

$router->addRoute('event_home', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri',
    [
        'module' => 'default',
        'controller' => 'index',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('home-contact-thanks', new Zend_Controller_Router_Route(
    '/@thankyou-for-sending-form',
    [
        'module' => 'default',
        'controller' => 'index',
        'action' => 'contact-thanks',
    ]
));

$router->addRoute('event_home-contact-thanks', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@thankyou-for-sending-form',
    [
        'module' => 'default',
        'controller' => 'index',
        'action' => 'contact-thanks',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
