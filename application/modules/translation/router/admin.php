<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_translation', new Zend_Controller_Router_Route(
    '/admin/translation',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'index',
    ]
));

$router->addRoute('admin_translation_new', new Zend_Controller_Router_Route(
    '/admin/translation/new',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'new',
    ]
));

$router->addRoute('admin_translation_edit', new Zend_Controller_Router_Route(
    '/admin/translation/edit/:hash',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_translation_status', new Zend_Controller_Router_Route(
    '/admin/translation/status/:hash',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_translation_delete', new Zend_Controller_Router_Route(
    '/admin/translation/delete/:hash',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('news_admin_delete-image', new Zend_Controller_Router_Route(
    '/admin/translation/delete-image/:hash',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'delete-image',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_translation_export', new Zend_Controller_Router_Route(
    '/admin/translation/export',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'export',
    ],
    [
    ]
));

$router->addRoute('admin_translation_export_list', new Zend_Controller_Router_Route(
    '/admin/translation/export/list',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'export-list',
    ],
    [
    ]
));

$router->addRoute('admin_translation_import', new Zend_Controller_Router_Route(
    '/admin/translation/import',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'import',
    ],
    [
    ]
));

$router->addRoute('admin_translation_upload', new Zend_Controller_Router_Route(
    '/admin/translation/upload',
    [
        'module' => 'translation',
        'controller' => 'admin',
        'action' => 'upload',
    ],
    [
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
