<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-sponsors', new Zend_Controller_Router_Route(
    '/admin/event/sponsors',
    [
        'module' => 'event',
        'controller' => 'admin-sponsors',
        'action' => 'index',
    ]
));

$router->addRoute('event_admin_sponsors_get-banner', new Zend_Controller_Router_Route(
    '/admin/event/sponsors/@route_get-banner',
    [
        'module' => 'event',
        'controller' => 'admin-sponsors',
        'action' => 'get-banner',
    ]
));
