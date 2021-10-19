<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('statistics_admin-index', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('statistics_admin-events', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/events',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'events',
    ]
));

$router->addRoute('statistics_admin-event', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/event',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'event',
    ]
));
$router->addRoute('statistics_admin-event_details', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/event-details/:hash',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'event-details',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('statistics_admin-gamification', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/gamification',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'gamification',
    ]
));
$router->addRoute('statistics_admin-gamification2', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/gamification2',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'gamification2',
    ]
));
$router->addRoute('statistics_admin-gamification-convert-points', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/gamification-convert-points',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'gamification-convert-points',
    ]
));
$router->addRoute('statistics_admin-gamification-user-points', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/gamification-user-points/:hash',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'gamification-user-points',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('statistics_admin-export_event', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/export_event',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'export-event',
    ]
));
$router->addRoute('statistics_admin-export_stand', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/export_stand',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'export-stand',
    ]
));

$router->addRoute('statistics_admin-download-stand-files', new Zend_Controller_Router_Route(
    '/@route_admin/statististics/download-stand-files/:hash',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'download-stand-files',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('statistics_admin-download-event-file', new Zend_Controller_Router_Route(
    '/@route_admin/statististics/download-event-file',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'download-event-file',
    ]
));

$router->addRoute('statistics_admin-stand-view', new Zend_Controller_Router_Route(
    '/@route_admin/statististics/stand-view/:hash',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'stand-view',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('statistics_admin-history', new Zend_Controller_Router_Route(
    '/@route_admin/statististics/history/:type/:hash/',
    [
        'module' => 'statistics',
        'controller' => 'admin',
        'action' => 'statistics-history',
    ],
    [
        'type' => '(.*)',
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_participation_import', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/import/:start_import',
    [
        'module' => 'statistics',
        'controller' => 'admin-import',
        'action' => 'index',
        'start_import' => '',
    ],
    [
        'start_import' => '(.*)',
    ]
));

$router->addRoute('admin_participation_import-to-event', new Zend_Controller_Router_Route(
    '/@route_admin/statistics/import/:event_hash',
    [
        'module' => 'statistics',
        'controller' => 'admin-import',
        'action' => 'import-to-event',
    ],
    [
        'event_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
