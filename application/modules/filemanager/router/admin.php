<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('site_files', new Zend_Controller_Router_Route(
    '/admin/files/:mode',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'index',
        'mode' => 'list',
    ],
    [
        'mode' => '(list|get|getImage)',
    ]
));

$router->addRoute('site_files_operation', new Zend_Controller_Router_Route(
    '/admin/files/operation',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'operation',
    ]
));

$router->addRoute('site_files_newCatalog', new Zend_Controller_Router_Route(
    '/admin/files/catalog/new',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'new-catalog',
    ]
));

$router->addRoute('site_files_nameEdit', new Zend_Controller_Router_Route(
    '/admin/files/name/edit',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'name-edit',
    ]
));

$router->addRoute('site_files_uploadFile', new Zend_Controller_Router_Route(
    '/admin/files/uploadFile',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'upload-file',
    ]
));

$router->addRoute('site_files_fileInfo', new Zend_Controller_Router_Route_Static(
    '/admin/files/fileInfo',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'file-info',
    ]
));

$router->addRoute('site_files_editImage', new Zend_Controller_Router_Route_Static(
    '/admin/files/editImage',
    [
        'module' => 'filemanager',
        'controller' => 'admin',
        'action' => 'edit-image',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
