<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 21.10.13
 * Time: 13:05
 * To change this template use File | Settings | File Templates.
 */
defined('_SERVICE_ACCESS') || die('Access Denied');

$config['shop']['enabled'] = true;
$config['shop']['name'] = 'Shop';

$config['shop']['navigation']['admin'] = [
    [
        'label' => 'c',
        'route' => 'shop_admin',
        'resource' => 'shop_admin_index',
    ],
    [
        'label' => 'nav_cms_shop_location',
        'route' => 'shop_admin-location',
        'resource' => 'shop_admin-location_index',
    ],
];

return $config;
