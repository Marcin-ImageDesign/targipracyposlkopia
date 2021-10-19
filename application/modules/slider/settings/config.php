<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['slider']['enabled'] = true;
$config['slider']['name'] = 'Slider';

$config['slider']['navigation']['admin'] = [
    [
        'label' => 'Slider Manager',
        'route' => 'admin_slider-category',
        'resource' => 'slider_admin-category',
        'pages' => [
            [
                'label' => 'Edytuj kategorię',
                'route' => 'admin_slider-category_edit',
                'resource' => 'slider_admin-category_edit',
                'visible' => false,
            ],
            [
                'label' => 'Add new category',
                'route' => 'admin_slider-category_new',
                'resource' => 'slider_admin-category_new',
            ],
            [
                'label' => 'Lista sliderów',
                'route' => 'admin_slider',
                'resource' => 'slider_admin',
                'visible' => false,
            ],
            [
                'label' => 'Edycja slidera',
                'route' => 'admin_slider_edit',
                'resource' => 'slider_admin_edit',
                'visible' => false,
            ],
            [
                'label' => 'Dodaj nowy slider',
                'route' => 'admin_slider_new',
                'resource' => 'slider_admin_new',
                'visible' => false,
            ],
        ],
    ],
];

return $config;
