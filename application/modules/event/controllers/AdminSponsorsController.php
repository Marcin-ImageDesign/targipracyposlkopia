<?php

class Event_AdminSponsorsController extends Engine_Controller_Admin
{
    public function indexAction()
    {
        if ($this->hasSelectedEvent()) {
            $eventSponsorsForm = new Event_Form_Admin_Sponsors(['event' => $this->getSelectedEvent()]);

            if ($this->_request->isPost() && $eventSponsorsForm->isValid($this->_request->getPost())) {
                $this->_flash->success->addMessage('Save successfully completed');
                $this->_redirector->gotoRouteAndExit([], 'event_admin-sponsors');
            }

            $this->view->event = $this->getSelectedEvent();
            $this->view->eventSponsorsForm = $eventSponsorsForm;
        }
    }

    public function getBannerAction()
    {
        $key = time();
        $banner = new Event_Form_Admin_Sponsors_Banner([
            'data' => ['name' => '', 'link' => '', 'target' => '', 'image' . $key => '', 'style' => '', 'image' => ''],
            'key' => $key,
        ]);

        $banner->setElementsBelongTo('EventFormAdminSponsors[sponsors][' . $key . ']');

        $this->jsonResult['result'] = true;
        $this->jsonResult['html'] = $banner->render();
    }
}
