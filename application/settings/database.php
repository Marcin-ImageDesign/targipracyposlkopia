<?php

defined('_SERVICE_ACCESS') or die('Access Denied');

$configPath = ROOT_PATH . '/config.php';
$config = file_exists($configPath) ? include $configPath : null;

return [
    'type' => 'mysql',
    'host' => $config['database_host'] ?? '',
    'user' => $config['database_user'] ?? '',
    'pass' => $config['database_password'] ?? '',
    'base' => $config['database_name'] ?? '',
    'options' => [
        'pearStyle' => true,
        'baseClassPrefix' => 'Table_',
        'baseClassesDirectory' => '',
        'baseClassName' => 'Engine_Doctrine_Record',
    ],
    'attributes' => [
        Doctrine::ATTR_QUOTE_IDENTIFIER => true,
        Doctrine::ATTR_TABLE_CLASS => 'Engine_Doctrine_Table',
    ],
];
