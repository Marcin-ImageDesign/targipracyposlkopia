<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('event_stand', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_info', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand-info/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'info',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_contact', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_contact/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'contact',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_contact_chat', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_contact_chat/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'contactchat',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_facebook', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_facebook/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'facebook',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_share_facebook', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_share_facebook/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'share-facebook',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_skype', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_skype/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'skype',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_share_googleplus', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_share_googleplus/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'share-googleplus',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));
$router->addRoute('event_stand_share_twitter', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_share_twitter/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'share-twitter',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_files', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand_files/:stand_uri',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'files',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_chat', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/chat',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'chat',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_preview', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/preview',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'preview',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

$router->addRoute('event_stand_www-site', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/:hall_uri/@route_event_stand/:stand_uri/www-site',
    [
        'module' => 'event',
        'controller' => 'stand',
        'action' => 'www-site',
    ],
    [
        'event_uri' => '(.*)',
        'hall_uri' => '(.*)',
        'stand_uri' => '(.*)',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
