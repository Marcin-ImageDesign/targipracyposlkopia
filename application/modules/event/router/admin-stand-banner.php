<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-stand-banner', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_stand_banner',
    [
        'module' => 'event',
        'controller' => 'admin-stand-banner',
        'action' => 'index',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-banner_delete', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_stand_banner/:banner_key',
    [
        'module' => 'event',
        'controller' => 'admin-stand-banner',
        'action' => 'delete',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'banner_key' => '.*',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
