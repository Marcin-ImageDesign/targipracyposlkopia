<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-hallmaps', new Zend_Controller_Router_Route(
    '/admin/event/hallmaps',
    [
        'module' => 'event',
        'controller' => 'admin-hallmap',
        'action' => 'index',
    ]
));

$router->addRoute('event_admin-hallmap_edit', new Zend_Controller_Router_Route(
    '/admin/event/hallmap/:hallmap_hash',
    [
        'module' => 'event',
        'controller' => 'admin-hallmap',
        'action' => 'edit',
    ],
    [
        'hallmap_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-hallmap_new', new Zend_Controller_Router_Route(
    '/admin/event/hallmap/new',
    [
        'module' => 'event',
        'controller' => 'admin-hallmap',
        'action' => 'new',
    ]
));

$router->addRoute('event_admin-hall-map_get-banner', new Zend_Controller_Router_Route(
    '/admin/event/hallmap/@route_get-banner',
    [
        'module' => 'event',
        'controller' => 'admin-hallmap',
        'action' => 'get-banner',
    ]
));

$router->addRoute('event_admin-hallmap_assign', new Zend_Controller_Router_Route(
    '/admin/event/hallmap/assign/:hallmap_hash',
    [
        'module' => 'event',
        'controller' => 'admin-hallmap',
        'action' => 'assign',
    ]
));

$router->addRoute('event_admin-hallmap_delete', new Zend_Controller_Router_Route(
    '/admin/event/hallmap/delete/:hallmap_hash',
    [
        'module' => 'event',
        'controller' => 'admin-hallmap',
        'action' => 'delete',
    ]
));
