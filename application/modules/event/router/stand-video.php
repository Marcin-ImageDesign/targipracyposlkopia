<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_stand-video_index', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/@route_stand_video_list',
    [
        'module' => 'event',
        'controller' => 'stand-video',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand-video_details', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/@route_stand_video_details/:video_hash',
    [
        'module' => 'event',
        'controller' => 'stand-video',
        'action' => 'details',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'video_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
