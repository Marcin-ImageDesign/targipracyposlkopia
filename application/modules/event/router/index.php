<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('events', new Zend_Controller_Router_Route(
    '/@route_events',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'index',
    ]
));

$router->addRoute('event_hall', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_hall',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'hall',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_share_facebook', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_share_facebook',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'share-facebook',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_hall_uri', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_hall/:hall_uri',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'hall',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
    ]
));

$router->addRoute('event_programs', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/program',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'programs',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_rules', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/regulamin',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'rules',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_speakers', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/prelegenci',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'speakers',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_sponsors', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/partnerzy',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'sponsors',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_page', new Zend_Controller_Router_Route(
    '/@route_event?page=:page',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'sponsors',
    ],
    [
        'page' => '(d+)',
    ]
));

$router->addRoute('event_site', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_site/:site_uri/:hide_title',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'site',
        'hide_title' => false,
    ],
    [
        'event_uri' => '(.*)',
        'site_uri' => '(.*)',
        'hide_title' => '(.*)',
    ]
));

$router->addRoute('event_exhibitors', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_exhibitors/',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'exhibitors',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_reception', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@event_reception/',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'reception',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_webinar_view', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@webinar/:id_webinar/:md5_webinar',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'webinar-view',
    ],
    [
        'event_uri' => '(.*)',
        'id_webinar' => '\d+',
        'md5_webinar' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_files', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_files',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'files',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_download_file', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_download_file/:hash',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'download-event-file',
    ],
    [
        'event_uri' => '(.*)',
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_gamification', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_gamification',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'gamification',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_ranking', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_event_ranking',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'ranking',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('day_ranking', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_day_ranking',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'day-ranking',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_offer_catalogue', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_offer_catalogue',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'product-catalogue',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_offer_login', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_offer_login',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'offer-login',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_hall_stands_preview', new Zend_Controller_Router_Route(
    '/@route_event/:event_hash/:hall_uri/@route_event_hall-stands-preview/:is_template',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'hall-stands-preview',
        'is_template' => 0,
    ],
    [
        'event_hash' => '(.*)',
        'hall_uri' => '(.*)',
        'is_template' => '(.*)',
    ]
));

$router->addRoute('event_download_stand_file', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@event_download_stand_file/:hash',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'download-file',
    ],
    [
        'event_uri' => '(.*)',
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('event_hall_change', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@hall_change',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'hall-change',
        'hall_uri' => '',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
    ]
));

$router->addRoute('event_webinar', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@webinar',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'webinar',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

$router->addRoute('event_check_external_id', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@check_external_id/:user_hash',
    [
        'module' => 'event',
        'controller' => 'index',
        'action' => 'check-external-id',
    ],
    [
        'event_uri' => '(.*)',
        'user_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
