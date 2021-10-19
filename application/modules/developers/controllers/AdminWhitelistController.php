<?php

class Developers_AdminWhitelistController extends Engine_Controller_Admin
{
    private $_model;

    public function preDispatch()
    {
        parent::preDispatch();
        if (!DEBUG) {
            $this->forward403Unless('Access only for developers');
        }
    }

    public function indexAction()
    {
        $this->view->whitelist = Doctrine_Query::create()
            ->from('IpWhitelist')
            ->execute()
        ;
    }

    public function addAction()
    {
        $this->_model = new IpWhitelist();

        $this->_form();
    }

    public function editAction()
    {
        $this->_model = IpWhitelist::findOneByIdIpWhitelist($this->getParam('id'));

        $this->_form();
    }

    public function deleteAction()
    {
        $this->_model = IpWhitelist::findOneByIdIpWhitelist($this->getParam('id'));
        $this->_model->delete();
        IpWhitelist::updateWhitelistCache();

        $this->_flash->succes->addMessage($this->view->translate('cms_message_delete_success'));
        $this->_redirector->gotoRouteAndExit([], 'developers_admin-whitelist');
    }

    private function _form()
    {
        $this->_helper->viewRenderer('form');

        $form = new Developers_Form_Whitelist(['model' => $this->_model]);

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $this->_model->save();
                IpWhitelist::updateWhitelistCache();
            }
            $this->_flash->succes->addMessage($this->view->translate('cms_message_save_success'));
            $this->_redirector->gotoRouteAndExit([], 'developers_admin-whitelist');
        }

        $this->view->form = $form;
    }
}
