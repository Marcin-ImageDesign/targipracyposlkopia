<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_admin-plan', new Zend_Controller_Router_Route(
    '/admin/event/plan/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'index',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_sort', new Zend_Controller_Router_Route(
    '/admin/event/plan/:hash/date/:date_hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'index',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'date_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_sort_clear', new Zend_Controller_Router_Route(
    '/admin/event/plan/:hash/clear/:clear',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'index',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'clear' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_new', new Zend_Controller_Router_Route(
    '/admin/event/plan/new/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'new',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_edit', new Zend_Controller_Router_Route(
    '/admin/event/plan/edit/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_delete', new Zend_Controller_Router_Route(
    '/admin/event/plan/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_status', new Zend_Controller_Router_Route(
    '/admin/event/plan/status/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin_date-edit', new Zend_Controller_Router_Route(
    '/admin/event/plan/:hash/:hash_date',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'edit-date',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        'hash_date' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin_date-delete', new Zend_Controller_Router_Route(
    '/admin/event/plan/date/delete/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'data-delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_admin-plan_new_date', new Zend_Controller_Router_Route(
    '/admin/event/plan/new/date/:hash',
    [
        'module' => 'event',
        'controller' => 'admin-plan',
        'action' => 'add-date',
    ],
    [
        'hash' => '([0-9a-f]{32})',
        //        'plan_hash' => '([0-9a-f]{32})'
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
