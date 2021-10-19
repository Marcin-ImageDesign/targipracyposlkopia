<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['admin']['enabled'] = true;
$config['admin']['name'] = 'Admin';

$config['admin']['navigation']['admin'] = [
    [
        'label' => 'nav-cms_home-page',
        'route' => 'admin',
        'resource' => 'admin_index',
    ],
];

//$config['admin']['navigation']['admin'] = array(
//    array(
//        'label' => 'Menu',
//        'route' => 'admin_menu',
//        'resource' => 'admin_menu',
//        'pages' => array(
//            array(
//                'label' => 'Add subpage',
//                'route' => 'admin_menu_new',
//                'resource' => 'admin_menu_new',
//            ),
//            array(
//                'label' => 'Edit page',
//                'route' => 'admin_menu_edit',
//                'resource' => 'admin_menu_edit',
//                'visible' => false,
//            ),
//        ),
//    ),
//
//    array(
//        'label' => 'Page settings',
//        'route' => 'admin_settings',
//        'resource' => 'admin_settings',
//    ),
//
//);

return $config;
