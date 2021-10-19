<?php

defined('_SERVICE_ACCESS') || die('Access Denied');

$config['company']['enabled'] = true;
$config['company']['name'] = 'Companies';

$config['company']['navigation']['admin'] = [
    [
        'label' => 'Companies list',
        'route' => 'company_admin',
        'resource' => 'company_admin',
        'pages' => [],
    ],
];

$config['company']['plugin']['company_admin_new'] = ['Company_Plugin_Address_Form', 'Company_Plugin_Brand_Form'];
$config['company']['plugin']['company_admin_edit'] = ['Company_Plugin_Address_Form', 'Company_Plugin_Brand_Form'];

return $config;
