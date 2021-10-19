<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('user_register_index', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@register',
    [
        'module' => 'user',
        'controller' => 'register',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_register_thx', new Zend_Controller_Router_Route(
    '/@route_events/:event_uri/@register/@route_register_thx',
    [
        'module' => 'user',
        'controller' => 'register',
        'action' => 'thx',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_register_send-activate-link', new Zend_Controller_Router_Route(
    '/@route_events/:event_uri/@route_register_send-activate-link',
    [
        'module' => 'user',
        'controller' => 'register',
        'action' => 'send-activate-link',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('user_register_activate-account', new Zend_Controller_Router_Route(
    '/@route_events/:event_uri/@register/@route_user_register_activate-account/:userConfirmRequestHash',
    [
        'module' => 'user',
        'controller' => 'register',
        'action' => 'activate-account',
    ],
    [
        'event_hash' => '([0-9a-f]{32})',
        'userConfirmRequestHash' => '([0-9a-f]{32})',
    ]
));

//$router->addRoute('register', new Zend_Controller_Router_Route(
//	'/@register',
//	array(
//		'module' => 'user',
//		'controller' => 'register',
//		'action' => 'index',
//	)
//));
//
//$router->addRoute('register_thx', new Zend_Controller_Router_Route(
//	'/@register/@thx',
//	array(
//		'module' => 'user',
//		'controller' => 'register',
//		'action' => 'thx',
//	)
//));
//

Zend_Controller_Front::getInstance()->setRouter($router);
