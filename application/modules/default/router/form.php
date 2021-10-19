<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('route_form_get', new Zend_Controller_Router_Route(
    '/@route_form/@get/:className/:baseUser/:exhibStand/:standProduct/:formAction/:formId/:user',
    [
        'module' => 'default',
        'controller' => 'form',
        'action' => 'get',
        'className' => 'none',
        'baseUser' => 'none',
        'exhibStand' => 'none',
        'standProduct' => 'none',
        'formAction' => 'none',
        'formId' => 'none',
        'user' => 'none',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
