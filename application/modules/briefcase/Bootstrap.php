<?php

/**
 * @author Robert Roginski <rroginski@voxoft.com>
 */
class Briefcase_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initAcl()
    {
        $acl = Engine_Acl::getInstance();
        $acl->getResourcesForModule('briefcase');
    }

    public function _initBriefcase()
    {
        Zend_Controller_Front::getInstance()->registerPlugin(new Private_Controller_Plugin_Briefcase());
    }
}
