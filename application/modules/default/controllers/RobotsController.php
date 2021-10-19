<?php

class RobotsController extends Engine_Controller_Frontend
{
    public function indexAction()
    {
        $this->_redirect('/robots.txt', ['code' => 301]);
    }

    public function robotsAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getResponse()
            ->setHeader('Content-type', 'text/plain')
        ;

        $robots = Engine_Variable::getInstance()->getVariable('robots');

        $this->view->robots = $robots;
    }
}
