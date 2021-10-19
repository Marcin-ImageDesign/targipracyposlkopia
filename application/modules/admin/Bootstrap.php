<?php

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __initAcl()
    {
        $acl = Engine_Acl::getInstance();
        $acl->getResourcesForModule('admin');
    }
}
