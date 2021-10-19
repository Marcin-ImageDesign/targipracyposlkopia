<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 21.10.13
 * Time: 12:56
 * To change this template use File | Settings | File Templates.
 */
class Shop_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAcl()
    {
        $acl = Engine_Acl::getInstance();
        $acl->getResourcesForModule('shop');
    }
}
