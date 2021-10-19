<?php



class Event_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initNotificatoinPlugin()
    {
        Zend_Controller_Front::getInstance()->registerPlugin(new Private_Controller_Plugin_Notification());
    }

    protected function _initAcl()
    {
        $acl = Engine_Acl::getInstance();
        $acl->getResourcesForModule('event');
    }

    protected function _initEventPlugin()
    {
        Zend_Controller_Front::getInstance()->registerPlugin(new Private_Controller_Plugin_Event());
    }
}
