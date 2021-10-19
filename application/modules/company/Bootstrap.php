<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bootstrap.
 *
 * @author marcin
 */
class Company_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initAcl()
    {
        $acl = Engine_Acl::getInstance();
        $acl->getResourcesForModule('company');
    }
}
