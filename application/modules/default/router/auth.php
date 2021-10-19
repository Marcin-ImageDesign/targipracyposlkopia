<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('password-recover', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@remember',
    [
        'module' => 'default',
        'controller' => 'auth',
        'action' => 'recover-password',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('password-recovery_thx', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@remember/@mail',
    [
        'module' => 'default',
        'controller' => 'auth',
        'action' => 'send',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('password_recover_hash', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@recovery/:hash',
    [
        'module' => 'default',
        'controller' => 'auth',
        'action' => 'password-reset',
    ],
    [
        'hash' => '([0-9a-z]{32})',
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('password_recover_validity', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@recovery/@fail',
    [
        'module' => 'default',
        'controller' => 'auth',
        'action' => 'invalid-hash',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
