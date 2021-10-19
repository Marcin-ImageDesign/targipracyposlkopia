<?php

class Admin_SettingsController extends Engine_Controller_Admin
{
    /**
     * @var int
     */
    private $eventId;

    public function postDispatch()
    {
        parent::postDispatch();
        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('settings/_contentNav.phtml', 'nav-left');
        }
    }

    public function mainPageAction()
    {
        // formularz ustawień
        $formSettings = new Admin_Form_Settings_MainPageEvent();
        $this->eventId = Engine_Variable::getInstance()->getVariable('home_page_event_id');
        $formSettings->populate(['event' => $this->eventId]);

        if ($this->_request->isPost() && $formSettings->isValid($this->_request->getPost())) {
            $this->_flash->succes->addMessage('Save successfully completed');
            $this->_redirector->gotoRouteAndExit([], 'admin_settings-main');
        }

        $this->view->formSettings = $formSettings;
    }

    public function mainPageBoxesAction()
    {
        if ($this->hasSelectedEvent()) {
            $homePage = $this->getSelectedEvent()->getHomePage($this->getSelectedLanguage());
            $homePageBoxesForm = new Admin_Form_Settings_MainPageBox($homePage);

            if ($this->_request->isPost() && $homePageBoxesForm->isValid($this->_request->getPost())) {
                $this->_flash->succes->addMessage('Save successfully completed');
                $this->_redirector->gotoRouteAndExit([], 'admin_settings-main-boxes');
            }

            $this->view->event = $this->getSelectedEvent();
            $this->view->homePageBoxesForm = $homePageBoxesForm;
        }
    }
}
