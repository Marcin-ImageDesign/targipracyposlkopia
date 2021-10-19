<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Robert Rogiński <rroginski@voxcommerce.pl>
 * Date: 20.03.13
 * Time: 10:34.
 */
$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('developers_admin-whitelist', new Zend_Controller_Router_Route(
    '/admin/developers/whitelist',
    [
        'module' => 'developers',
        'controller' => 'admin-whitelist',
        'action' => 'index',
    ]
));
$router->addRoute('developers_admin-whitelist-add', new Zend_Controller_Router_Route(
    '/admin/developers/whitelist/add',
    [
        'module' => 'developers',
        'controller' => 'admin-whitelist',
        'action' => 'add',
    ]
));
$router->addRoute('developers_admin-whitelist-edit', new Zend_Controller_Router_Route(
    '/admin/developers/whitelist/edit/:id',
    [
        'module' => 'developers',
        'controller' => 'admin-whitelist',
        'action' => 'edit',
    ]
));
$router->addRoute('developers_admin-whitelist-delete', new Zend_Controller_Router_Route(
    '/admin/developers/whitelist/delete/:id',
    [
        'module' => 'developers',
        'controller' => 'admin-whitelist',
        'action' => 'delete',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
