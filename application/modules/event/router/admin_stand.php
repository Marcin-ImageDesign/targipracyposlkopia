<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-stand_index', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stands',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'index',
    ]
));

$router->addRoute('event_admin-stand_new', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/@route_new',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'new',
    ]
));

$router->addRoute('event_admin-stand_edit', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/@route_edit/:stand_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'edit',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand_generate-number', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stands/@generate_number',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'generate-number',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand_stend-view-file-delete', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:stand_hash/@route_stend-view-file-delete/:stand_view_file_type',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'stand-view-file-delete',
        'event_hash' => '',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
        'stand_view_file_type' => '(.*)',
    ]
));

$router->addRoute('admin_participation-stands_stand-status', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stands/stand-status/:stand_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'stand-status',
    ],
    [
        'stand_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-preview-stand-iframe', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/:event_uri/:hall_uri/:stand_uri/@route_event_admin-firame_stand-index',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'preview-stand-iframe',
    ],
    [
        'event_uri' => '(.*)',
        'stand_uri' => '(.*)',
        'hall_uri' => '(.*)',
    ]
));

$router->addRoute('event_admin-hall-stands-preview-iframe', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/@route_event_admin-iframe-hall-stands/:event_hash/:hall_uri/:is_template',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'preview-hall-stands-iframe',
        'is_template' => 0,
    ],
    [
        'event_hash' => '(.*)',
        'hall_uri' => '(.*)',
        'is_template' => '(.*)',
    ]
));

$router->addRoute('event_admin-stand-delete', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'delete',
    ],
    [
        'hash' => '(.*)',
    ]
));

$router->addRoute('event_admin-stand-clone', new Zend_Controller_Router_Route(
    '/@route_admin/@route_stand/clone/:stand_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand',
        'action' => 'clone',
    ],
    [
        'stand_hash' => '(.*)',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
