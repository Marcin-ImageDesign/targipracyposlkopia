<?php

$router = Zend_Controller_Front::getInstance()->getRouter();

$participationTypeList = Doctrine_Query::create()
    ->from('ExhibParticipationType ept INDEXBY ept.id_exhib_participation_type')
    ->execute([], Doctrine::HYDRATE_ARRAY)
;

foreach ($participationTypeList as $k => $v) {
//    var_dump( $k, $v );
    $router->addRoute('user_admin-participation_list-' . $k, new Zend_Controller_Router_Route(
        '/@route_admin/@route_user/@participation/@route_participation_' . $v['type'],
        [
            'module' => 'user',
            'controller' => 'admin-participation',
            'action' => 'list' . $k,
            'id_exhib_participation_type' => $k,
        ]
    ));

    $router->addRoute('user_admin-participation_new-' . $k, new Zend_Controller_Router_Route(
        '/@route_admin/@route_user/@participation/@route_participation_new-' . $v['type'],
        [
            'module' => 'user',
            'controller' => 'admin-participation',
            'action' => 'new' . $k,
            'id_exhib_participation_type' => $k,
        ]
    ));
}
//exit;
$router->addRoute('user_admin-participation_delete', new Zend_Controller_Router_Route(
    '/@route_admin/@route_user/@participation/@route_participation_delete/:exhib_participation_hash',
    [
        'module' => 'user',
        'controller' => 'admin-participation',
        'action' => 'delete',
    ],
    [
        'exhib_participation_hash' => '([0-9a-f]{32})',
    ]
));

$router->addRoute('user_admin-participation_status', new Zend_Controller_Router_Route(
    '/@route_admin/@route_user/@participation/@route_status/:exhib_participation_hash',
    [
        'module' => 'user',
        'controller' => 'admin-participation',
        'action' => 'status',
    ],
    [
        'exhib_participation_hash' => '([0-9a-f]{32})',
    ]
));

Zend_Controller_Front::getInstance()->setRouter($router);
