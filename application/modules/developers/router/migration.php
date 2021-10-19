<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Robert Rogiński <rroginski@voxcommerce.pl>
 * Date: 20.03.13
 * Time: 10:34.
 */
$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('developers_migration_get-images', new Zend_Controller_Router_Route(
    '/admin/developers/migration/get-images',
    [
        'module' => 'developers',
        'controller' => 'migration',
        'action' => 'get-images',
    ]
));

$router->addRoute('developers_migration_image-banner-v3', new Zend_Controller_Router_Route(
    '/admin/developers/migration/image-banner-v3',
    [
        'module' => 'developers',
        'controller' => 'migration',
        'action' => 'image-banner-v3',
    ]
));

$router->addRoute('developers_migration_stand-view-image', new Zend_Controller_Router_Route(
    '/admin/developers/migration/stand-view-image',
    [
        'module' => 'developers',
        'controller' => 'migration',
        'action' => 'stand-view-image',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
