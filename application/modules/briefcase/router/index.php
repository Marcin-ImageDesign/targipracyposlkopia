<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('briefcase', new Zend_Controller_Router_Route(
    '/@route_event/:event_uri/@route_briefcase',
    [
        'module' => 'briefcase',
        'controller' => 'index',
        'action' => 'index',
    ],
    [
        'event_uri' => '(.*)',
    ]
));

//$router->addRoute('briefcase', new Zend_Controller_Router_Route(
//    '/@route_briefcase/:id_namespace',
//    array(
//        'module' => 'briefcase',
//        'controller' => 'index',
//        'action' => 'index',
//        'id_namespace' => 0,
//    ),
//    array(
//        'id_namespace' => '\d+'
//    )
//));
//
//
//$router->addRoute('briefcase_new-link', new Zend_Controller_Router_Route(
//    '/@route_briefcase/@route_briefcase_new',
//    array(
//        'module' => 'briefcase',
//        'controller' => 'index',
//        'action' => 'add-link'
//    )
//));
//
//
$router->addRoute('briefcase_add-element', new Zend_Controller_Router_Route(
    '/@route_briefcase/@route_briefcase_add_element/:id_briefcase_type/:element/:namespace',
    [
        'module' => 'briefcase',
        'controller' => 'index',
        'action' => 'add-element',
        'namespace' => '',
    ],
    [
        'id_briefcase_type' => '\d+',
        'element' => '.*',
        'namespace' => '\d+',
    ]
));

$router->addRoute('briefcase_remove-element', new Zend_Controller_Router_Route(
    '/@route_briefcase/@route_briefcase_remove_element/:id_briefcase_type/:element/:namespace',
    [
        'module' => 'briefcase',
        'controller' => 'index',
        'action' => 'remove-element',
        'namespace' => '',
    ],
    [
        'id_briefcase_type' => '\d+',
        'element' => '.*',
        'namespace' => '\d+',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
