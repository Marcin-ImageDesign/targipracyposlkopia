<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-patrons', new Zend_Controller_Router_Route(
    '/admin/event/patrons',
    [
        'module' => 'event',
        'controller' => 'admin-media-patrons',
        'action' => 'index',
    ]
));

$router->addRoute('event_admin_patrons_get-banner', new Zend_Controller_Router_Route(
    '/admin/event/patrons/@route_get-banner',
    [
        'module' => 'event',
        'controller' => 'admin-media-patrons',
        'action' => 'get-banner',
    ]
));
