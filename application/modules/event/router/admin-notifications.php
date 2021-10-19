<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_event-notifications', new Zend_Controller_Router_Route(
    '/@route_admin/event/@route_event_notifications',
    [
        'module' => 'event',
        'controller' => 'admin-notifications',
        'action' => 'index',
    ]
));

$router->addRoute('admin_event-notification_edit', new Zend_Controller_Router_Route(
    '/@route_admin/event/@route_notification-edit/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-notifications',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event-notification_new', new Zend_Controller_Router_Route(
    '/@route_admin/event/@route_notification-new',
    [
        'module' => 'event',
        'controller' => 'admin-notifications',
        'action' => 'new',
    ]
));

$router->addRoute('admin_event-notification_delete', new Zend_Controller_Router_Route(
    '/@route_admin/event/@route_notification-delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-notifications',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));
