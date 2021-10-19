<?php



class Statistics_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAcl()
    {
        $acl = Engine_Acl::getInstance();

        $acl->getResourcesForModule('statistics');
    }
}
