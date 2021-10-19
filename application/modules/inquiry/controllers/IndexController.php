<?php

class Inquiry_IndexController extends Engine_Controller_Frontend
{
    public function indexAction()
    {
    }

    public function messageAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->view->message = $this->getRequest()->getParam('message');
    }
}
