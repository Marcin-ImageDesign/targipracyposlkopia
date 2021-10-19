<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-stand-offer_index', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_offer-list',
    [
        'module' => 'event',
        'controller' => 'admin-stand-product',
        'action' => 'index',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-offer_edit', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_offer-edit/:product_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-product',
        'action' => 'edit',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'product_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-offer_new', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_offer-new',
    [
        'module' => 'event',
        'controller' => 'admin-stand-product',
        'action' => 'new',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-offer_delete', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_offer-delete/:product_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-product',
        'action' => 'delete',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'product_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_stand-offer_status', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_offer-status/:product_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-product',
        'action' => 'status',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'product_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-product-files_download', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/product/file/download/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-product',
        'action' => 'download',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
