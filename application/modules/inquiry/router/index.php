<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('inquiry', new Zend_Controller_Router_Route(
    '/@route_inquiry',
    [
        'module' => 'inquiry',
        'controller' => 'index',
        'action' => 'index',
    ]
));
$router->addRoute('inquiry_message', new Zend_Controller_Router_Route(
    '/@route_inquiry/message',
    [
        'module' => 'inquiry',
        'controller' => 'index',
        'action' => 'message',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
