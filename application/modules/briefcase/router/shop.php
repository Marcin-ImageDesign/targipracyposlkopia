<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('shop_index', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_mycart',
    [
        'module' => 'briefcase',
        'controller' => 'shop',
        'action' => 'index',
    ],
    [
        'event_uri' => '.*',
    ]
));

$router->addRoute('shop_add', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_mycart/@add',
    [
        'module' => 'briefcase',
        'controller' => 'shop',
        'action' => 'add',
    ],
    [
        'event_uri' => '.*',
    ]
));

$router->addRoute('shop_remove', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_mycart/@remove/:element',
    [
        'module' => 'briefcase',
        'controller' => 'shop',
        'action' => 'remove',
    ],
    [
        'event_uri' => '.*',
        'element' => '.*',
    ]
));

$router->addRoute('shop_order-summary', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_order_summary',
    [
        'module' => 'briefcase',
        'controller' => 'shop',
        'action' => 'order-summary',
    ],
    [
        'event_uri' => '.*',
    ]
));

$router->addRoute('shop_send-order', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_send_order',
    [
        'module' => 'briefcase',
        'controller' => 'shop',
        'action' => 'send-order',
    ],
    [
        'event_uri' => '.*',
    ]
));

$router->addRoute('shop_thx-send-order', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_send_order_thx',
    [
        'module' => 'briefcase',
        'controller' => 'shop',
        'action' => 'thx-send-order',
    ],
    [
        'event_uri' => '.*',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
