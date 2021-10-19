<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event-video_index', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_stand_video_list',
    [
        'module' => 'event',
        'controller' => 'video',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event-video_details', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_stand_video_details/:video_hash',
    [
        'module' => 'event',
        'controller' => 'video',
        'action' => 'details',
    ],
    [
        'event_uri' => '(.*)',
        'video_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
