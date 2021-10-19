<?php

class PagesController extends Engine_Controller_Frontend
{
    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender() && $this->_helper->layout->isEnabled()) {
            $this->view->selectedAction = $this->_request->getActionName();
            $this->view->renderToPlaceholder('pages/_nav_content.phtml', 'nav-content');
        }
    }

    public function indexAction()
    {
    }

    public function standDescAction()
    {
    }

    public function sponsoringAction()
    {
    }
}
