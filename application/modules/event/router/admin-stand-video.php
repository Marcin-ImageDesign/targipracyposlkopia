<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_stand-video_index', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_video-list',
    [
        'module' => 'event',
        'controller' => 'admin-stand-video',
        'action' => 'index',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-video_edit', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_video-edit/:video_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-video',
        'action' => 'edit',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'video_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-video_new', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_video-new',
    [
        'module' => 'event',
        'controller' => 'admin-stand-video',
        'action' => 'new',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-video_delete', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_video-delete/:video_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-video',
        'action' => 'delete',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'video_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-video_status', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_video-status/:video_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-video',
        'action' => 'status',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'video_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
