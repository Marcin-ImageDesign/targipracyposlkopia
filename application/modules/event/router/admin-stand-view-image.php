<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-stand-view-image', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand-view-image',
    [
        'module' => 'event',
        'controller' => 'admin-stand-view-image',
        'action' => 'index',
    ]
));

$router->addRoute('event_admin-stand-view-image_new', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand-view-image/@route_new',
    [
        'module' => 'event',
        'controller' => 'admin-stand-view-image',
        'action' => 'new',
    ]
));

$router->addRoute('event_admin-stand-view-image_edit', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand-view-image/@route_edit/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-view-image',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-view-image_public', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand-view-image/@route_public/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-view-image',
        'action' => 'public',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-view-image_get-banner', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand-view-image/@route_get-banner',
    [
        'module' => 'event',
        'controller' => 'admin-stand-view-image',
        'action' => 'get-banner',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
