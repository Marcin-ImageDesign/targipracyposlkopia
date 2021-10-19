<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router
    ->addRoute('home', new Zend_Controller_Router_Route(
        '/',
        [
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index',
        ]
    ))
    ->addRoute('login', new Zend_Controller_Router_Route(
        '/@route_event/:event_uri/@login',
        [
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'index',
        ],
        [
            'event_uri' => '(.*)',
        ]
    ))
    ->addRoute('logout', new Zend_Controller_Router_Route(
        '/@logout',
        [
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'logout',
        ]
    ))
    ->addRoute('cdn', new Zend_Controller_Router_Route_Regex(
        'cdn/(\d+)/([a-zA-Z0-9]{5})-(w|h|b|c|a|m|bi|n|o)-(\d+)-(\d+)-(.*)\.(jpg|gif|png|svg|webp)',
        [
            'module' => 'default',
            'controller' => 'image-cdn',
            'action' => 'serve',
            'format' => 'jpg',
            'resize' => 'b',
            'width' => '100',
            'height' => '100',
            'params' => '',
        ],
        [
            1 => 'id',
            2 => 'key',
            3 => 'resize',
            4 => 'width',
            5 => 'height',
            6 => 'params',
            7 => 'format',
        ],
        'cdn/%d/%s-%s-%d-%d-%s.%s'
    ))
    ->addRoute('image_manager', new Zend_Controller_Router_Route(
        '/image-manager/:action/:id/:class/*',
        [
            'module' => 'default',
            'controller' => 'image-manager',
        ]
    ))
    ->addRoute('image_cropper', new Zend_Controller_Router_Route(
        '/image-cropper/:id_image/:token/*',
        [
            'module' => 'default',
            'controller' => 'image-cropper',
            'action' => 'index',
        ],
        [
            'id_image' => '\d+',
        ]
    ))
    ->addRoute('chat', new Zend_Controller_Router_Route(
        '/chat',
        [
            'module' => 'default',
            'controller' => 'index',
            'action' => 'chat',
        ]
    ));

Zend_Controller_Front::getInstance()->setRouter($router);
