<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('admin_slider-category', new Zend_Controller_Router_Route(
    '/admin/slider-category',
    [
        'module' => 'slider',
        'controller' => 'admin-category',
        'action' => 'index',
    ]
));

$router->addRoute('admin_slider-category_new', new Zend_Controller_Router_Route(
    '/admin/slider-category/new',
    [
        'module' => 'slider',
        'controller' => 'admin-category',
        'action' => 'new',
    ]
));

$router->addRoute('admin_slider-category_edit', new Zend_Controller_Router_Route(
    '/admin/slider-category/edit/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin-category',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider-category_delete', new Zend_Controller_Router_Route(
    '/admin/slider-category/delete/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin-category',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider-category_status', new Zend_Controller_Router_Route(
    '/admin/slider-category/status/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin-category',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider', new Zend_Controller_Router_Route(
    '/admin/slider/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'index',
        'hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider_new', new Zend_Controller_Router_Route(
    '/admin/slider/new/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'new',
        'hash' => '',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider_edit', new Zend_Controller_Router_Route(
    '/admin/slider/edit/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'edit',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider_delete', new Zend_Controller_Router_Route(
    '/admin/slider/delete/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'delete',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider_status', new Zend_Controller_Router_Route(
    '/admin/slider/status/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'status',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider_img_delete', new Zend_Controller_Router_Route(
    '/admin/slider/delete-img/:hash',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'delete-img',
    ],
    [
        'hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('admin_slider_move', new Zend_Controller_Router_Route_Regex(
    'admin/slider/move/([0-9a-f]{32})/(.*)/([0-9a-f]{32})',
    [
        'module' => 'slider',
        'controller' => 'admin',
        'action' => 'move',
    ],
    [
        1 => 'hash_element_move',
        2 => 'type',
        3 => 'hash_element',
    ],
    'admin/slider/move'
));

Zend_Controller_Front::getInstance()->setRouter($router);
