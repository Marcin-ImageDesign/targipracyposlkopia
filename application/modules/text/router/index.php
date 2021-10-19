<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$router->addRoute('text', new Zend_Controller_Router_Route(
    '/text/:uri',
    [
        'module' => 'text',
        'controller' => 'index',
        'action' => 'index',
    ],
    [
        'uri' => '.*',
    ]
));

//$textList = Doctrine_Query::create()
//    ->from('TextTranslation')
//    ->fetchArray();
//
//foreach($textList as $v){
//    $router->addRoute('text_'.$v['uri'], new Zend_Controller_Router_Route(
//        '/'.$v['uri'],
//        array(
//            'module' => 'text',
//            'controller' => 'index',
//            'action' => 'index',
//            'uri' => $v['uri'],
//        )
//    ));
//}

Zend_Controller_Front::getInstance()->setRouter($router);
