<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_event-video_index', new Zend_Controller_Router_Route(
    '/@route_admin/event/@route_video-list',
    [
        'module' => 'event',
        'controller' => 'admin-event-video',
        'action' => 'index',
    ]
));

$router->addRoute('admin_event-video_edit', new Zend_Controller_Router_Route(
    '/@route_admin/event/:event_hash/@route_video-edit/:video_hash',
    [
        'module' => 'event',
        'controller' => 'admin-event-video',
        'action' => 'edit',
    ],
    [
        'video_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event-video_new', new Zend_Controller_Router_Route(
    '/@route_admin/event/:event_hash/@route_video-new',
    [
        'module' => 'event',
        'controller' => 'admin-event-video',
        'action' => 'new',
    ],
    [
        'event_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event-video_delete', new Zend_Controller_Router_Route(
    '/@route_admin/event/:event_hash/@route_video-delete/:video_hash',
    [
        'module' => 'event',
        'controller' => 'admin-event-video',
        'action' => 'delete',
    ],
    [
        'video_hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_event-video_status', new Zend_Controller_Router_Route(
    '/@route_admin/event/:event_hash/@route_video-status/:video_hash',
    [
        'module' => 'event',
        'controller' => 'admin-event-video',
        'action' => 'status',
    ],
    [
        'video_hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
