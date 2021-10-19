<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('inquiry_admin', new Zend_Controller_Router_Route(
    '/admin/@route_inquiry',
    [
        'module' => 'inquiry',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('inquiry_admin_sort', new Zend_Controller_Router_Route(
    '/admin/@route_inquiry/:inquiry_channel',
    [
        'module' => 'inquiry',
        'controller' => 'admin',
        'action' => 'index',
    ],
    [
        'inquiry_channel' => '(.*)',
    ]
));

$router->addRoute('inquiry_admin_view', new Zend_Controller_Router_Route(
    '/admin/@route_inquiry/view/:id',
    [
        'module' => 'inquiry',
        'controller' => 'admin',
        'action' => 'view',
    ],
    [
        'id' => '\d+',
    ]
));
$router->addRoute('inquiry_admin_delete', new Zend_Controller_Router_Route(
    '/admin/@route_inquiry/delete/:id',
    [
        'module' => 'inquiry',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'id' => '\d+',
    ]
));

$router->addRoute('inquiry_admin_get_attachments', new Zend_Controller_Router_Route(
    '/admin/@route_inquiry/attachment/:id',
    [
        'module' => 'inquiry',
        'controller' => 'admin',
        'action' => 'get-attachment',
    ],
    [
        'id' => '\d+',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
