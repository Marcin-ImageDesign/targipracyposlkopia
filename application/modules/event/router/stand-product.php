<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_stand-offer_index', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/@route_stand_offers_list',
    [
        'module' => 'event',
        'controller' => 'stand-product',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));
$router->addRoute('event_stand-offer_index_promoted', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/@promoted/:promoted/@route_stand_offers_list',
    [
        'module' => 'event',
        'controller' => 'stand-product',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'promoted' => '([0-9]{1})',
    ]
));

$router->addRoute('event_stand-offer_details', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/@route_stand_offer_details/:product_hash/:back_to',
    [
        'module' => 'event',
        'controller' => 'stand-product',
        'action' => 'details',
        'back_to' => '',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'product_hash' => '([0-9a-f]{32})',
        'back_to' => '(.*)',
    ]
));

$router->addRoute('event_stand-offer_apply', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/@route_stand_offer_apply/:product_hash',
    [
        'module' => 'event',
        'controller' => 'stand-product',
        'action' => 'apply',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'product_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_stand-offer_shop', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_stand/:stand_uri/@route_stand_offer_shop/:product_hash',
    [
        'module' => 'event',
        'controller' => 'stand-product',
        'action' => 'shop',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'product_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_stand-product-files_download', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/product/file/download/:hash',
    [
        'module' => 'event',
        'controller' => 'stand-product',
        'action' => 'download',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',

        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
