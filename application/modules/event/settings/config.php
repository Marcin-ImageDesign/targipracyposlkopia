<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['event']['enabled'] = true;
$config['event']['name'] = 'Event';

$config['event']['navigation']['admin'] = [
    [
        'label' => 'nav-cms_events',
        'route' => 'admin_event',
        'resource' => 'event_admin',
        'pages' => [
            [
                'label' => 'nav-cms_event_edit',
                'route' => 'admin_event_edit',
                'resource' => 'event_admin_edit',
                'visible' => false,
            ],
            [
                'label' => 'nav-cms_event_new',
                'route' => 'admin_event_new',
                'resource' => 'event_admin_new',
            ],
            [
                'label' => 'Lista podstron',
                'route' => 'event_admin-site',
                'resource' => 'event_admin-site',
                'visible' => false,
            ],
            [
                'label' => 'Nowa podstrona',
                'route' => 'event_admin-site_new',
                'resource' => 'event_admin-site_new',
                'visible' => false,
            ],
            [
                'label' => 'Edycja podstrony',
                'route' => 'event_admin-site_edit',
                'resource' => 'event_admin-site_edit',
                'visible' => false,
            ],
            [
                'label' => 'Plan of event',
                'route' => 'event_admin-plan',
                'resource' => 'event_admin-plan',
                'visible' => false,
                'pages' => [
                    [
                        'label' => 'Add new plan',
                        'route' => 'event_admin-plan_new',
                        'resource' => 'event_admin-plan_new',
                        'visible' => false,
                    ],
                    [
                        'label' => 'Edit plan',
                        'route' => 'event_admin-plan_edit',
                        'resource' => 'event_admin-plan_edit',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
];

//$config['event']['plugin']['admin_event_new'] = array('Event_Plugin_Speakers_Form', 'Event_Plugin_Sponsors_Form');
//$config['event']['plugin']['admin_event_edit'] = array('Event_Plugin_Speakers_Form', 'Event_Plugin_Sponsors_Form');

return $config;
