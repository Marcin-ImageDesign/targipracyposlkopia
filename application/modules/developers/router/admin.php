<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Robert Rogiński <rroginski@voxcommerce.pl>
 * Date: 20.03.13
 * Time: 10:34.
 */
$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('developers_admin', new Zend_Controller_Router_Route(
    '/admin/developers',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('developers_admin_adminer', new Zend_Controller_Router_Route(
    '/admin/developers/adminer.php',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'adminer',
    ]
));

$router->addRoute('developers_admin_changelog', new Zend_Controller_Router_Route(
    '/admin/developers/changelog',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'changelog',
    ]
));

$router->addRoute('developers_admin_clear-cache', new Zend_Controller_Router_Route(
    '/admin/developers/clear-cache',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'clear-cache',
    ]
));

$router->addRoute('developers_admin_logs', new Zend_Controller_Router_Route(
    '/admin/developers/logs',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'logs',
    ]
));

$router->addRoute('developers_admin_get-file', new Zend_Controller_Router_Route(
    '/admin/developers/get-file',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'get-file',
    ]
));

$router->addRoute('developers_admin_generate-hall-map', new Zend_Controller_Router_Route(
    '/admin/developers/generate-hall-map',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'generate-hall-map',
    ]
));

$router->addRoute('developers_admin_generate-home-page', new Zend_Controller_Router_Route(
    '/admin/developers/generate-home-page',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'generate-home-page',
    ]
));

$router->addRoute('developers_admin_generate-sponsors', new Zend_Controller_Router_Route(
    '/admin/developers/generate-sponsors',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'generate-sponsors',
    ]
));

$router->addRoute('developers_admin_ftp', new Zend_Controller_Router_Route(
    '/admin/developers/ftp',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'ftp',
    ]
));

$router->addRoute('developers_admin_variables', new Zend_Controller_Router_Route(
    '/admin/developers/variables',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'variables',
    ]
));

$router->addRoute('developers_admin_set-stand-icons', new Zend_Controller_Router_Route(
    '/admin/developers/stand-icons',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'set-stand-icons',
    ]
));

$router->addRoute('developers_admin_add-icon-to-stand', new Zend_Controller_Router_Route(
    '/admin/developers/add-icon-to-stand/:id_from/:id_to/:x/:y',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'add-icon-to-stand',
    ],
    [
        'id_from' => '^(\d+)',
        'id_to' => '^(\d+)',
        'x' => '^(\d+)',
        'y' => '^(\d+)',
    ]
));

$router->addRoute('developers_admin_update-acl', new Zend_Controller_Router_Route(
    '/admin/developers/update-acl',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'update-acl-resources',
    ]
));
$router->addRoute('developers_admin_add-new-res', new Zend_Controller_Router_Route(
    '/admin/developers/add-new-res',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'add-missing-resources',
    ]
));
$router->addRoute('developers_admin_remove-missing-res', new Zend_Controller_Router_Route(
    '/admin/developers/remove-missing-res',
    [
        'module' => 'developers',
        'controller' => 'admin',
        'action' => 'remove-missing-resources',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
