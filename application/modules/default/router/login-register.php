<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 10.10.13
 * Time: 11:45
 * To change this template use File | Settings | File Templates.
 */
$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('default_login-register_index', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@login-register',
    [
        'module' => 'default',
        'controller' => 'login-register',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('login-facebook', new Zend_Controller_Router_Route(
    '/login/facebook',
    [
        'module' => 'default',
        'controller' => 'login-register',
        'action' => 'login-facebook',
    ]
));
