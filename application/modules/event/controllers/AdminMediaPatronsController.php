<?php

class Event_AdminMediaPatronsController extends Engine_Controller_Admin
{
    public function indexAction()
    {
        if ($this->hasSelectedEvent()) {
            $eventPatronsForm = new Event_Form_Admin_MediaPatrons(['event' => $this->getSelectedEvent()]);

            if ($this->_request->isPost() && $eventPatronsForm->isValid($this->_request->getPost())) {
                $this->_flash->success->addMessage('Save successfully completed');
                $this->_redirector->gotoRouteAndExit([], 'event_admin-patrons');
            }

            $this->view->event = $this->getSelectedEvent();
            $this->view->eventPatronsForm = $eventPatronsForm;
        }
    }

    public function getBannerAction()
    {
        $key = time();
        $banner = new Event_Form_Admin_MediaPatrons_Banner([
            'data' => ['name' => '', 'link' => '', 'target' => '', 'image' . $key => '', 'style' => '', 'image' => ''],
            'key' => $key,
        ]);

        $banner->setElementsBelongTo('EventFormAdminMediaPatrons[patrons][' . $key . ']');

        $this->jsonResult['result'] = true;
        $this->jsonResult['html'] = $banner->render();
    }
}
