<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marek Skotarek
 * Date: 25.06.13
 * Time: 13:46
 * To change this template use File | Settings | File Templates.
 */
$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('developers_log_index', new Zend_Controller_Router_Route(
    '/admin/developers/log',
    [
        'module' => 'developers',
        'controller' => 'log',
        'action' => 'index',
    ]
));

$router->addRoute('developers_log_logs', new Zend_Controller_Router_Route(
    '/admin/developers/log/logs',
    [
        'module' => 'developers',
        'controller' => 'log',
        'action' => 'logs',
    ]
));
