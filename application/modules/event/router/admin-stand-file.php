<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-stand-files_index', new Zend_Controller_Router_Route(
    '/@route_admin/stand/:hash/files/:event_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-file',
        'action' => 'index',
        'event_hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-files_new', new Zend_Controller_Router_Route(
    '/@route_admin/stand/:hash/file/new/:event_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-file',
        'action' => 'new',
        'event_hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-files_edit', new Zend_Controller_Router_Route(
    '/@route_admin/stand/:hash/file/edit/:event_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-file',
        'action' => 'edit',
        'event_hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-files_download', new Zend_Controller_Router_Route(
    '/@route_admin/stand/:hash/file/download/:event_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-file',
        'action' => 'download',
        'event_hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-stand-files_delete', new Zend_Controller_Router_Route(
    '/@route_admin/stand/:hash/file/delete/:event_hash',
    [
        'module' => 'event',
        'controller' => 'admin-stand-file',
        'action' => 'delete',
        'event_hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'event_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
