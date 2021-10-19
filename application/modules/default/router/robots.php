<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('robots', new Zend_Controller_Router_Route_Static(
    '/robots.txt',
    [
        'module' => 'default',
        'controller' => 'robots',
        'action' => 'robots',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
