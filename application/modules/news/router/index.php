<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_articles', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@articles',
    [
        'module' => 'news',
        'controller' => 'index',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_chat-schedule', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_chat_schedule',
    [
        'module' => 'news',
        'controller' => 'index',
        'action' => 'chat-schedule',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_news', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@news/:news_uri',
    [
        'module' => 'news',
        'controller' => 'index',
        'action' => 'news',
    ],
    [
        'news_uri' => '(.*)',
        'event_uri' => '(.*)',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
