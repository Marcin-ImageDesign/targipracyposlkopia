<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Robert Rogiński <rroginski@voxcommerce.pl>
 * Date: 20.03.13
 * Time: 10:26.
 */
defined('_SERVICE_ACCESS') || die('Access Denied');

$config['developers']['enabled'] = true;
$config['developers']['name'] = 'Developers';

$config['developers']['navigation']['admin'] = [
    [
        'label' => 'nav_cms_developers',
        'route' => 'developers_admin',
        'resource' => 'developers_admin_index',
        'pages' => [
            [
                'label' => 'nav_cms_developers_adminer',
                'route' => 'developers_admin_adminer',
                'resource' => 'developers_admin_adminer',
            ],
        ],
    ],
];

return $config;
