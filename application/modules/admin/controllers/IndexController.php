<?php

class Admin_IndexController extends Engine_Controller_Admin
{
    public function init()
    {
    }

    public function indexAction()
    {
    }

    public function selectEventAction()
    {
        $eventList = $this->userAuth->getUserHasEvents();
        $this->view->eventList = $eventList;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_admin_select-event'));
        $this->view->return = $this->getParam('return', '/admin');
    }

    public function languageListAction()
    {
        $this->view->selectedLanguage = $this->getSelectedLanguage();
        $this->view->baseUserLanguageList = $this->getSelectedBaseUser()->getBaseUserLanguage();
    }
}
