<?php

class Event_AdminStandBannerController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Admin_StandBanner|mixed
     */
    public $form;
    /**
     * @var ExhibStand
     */
    private $_exhibStand;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_exhibStand = ExhibStand::findOneByHashAndBaseUser($this->_getParam('stand_hash'), $this->getSelectedBaseUser());
        $this->checkExhibStandAccess();

        $this->view->exhibStand = $this->_exhibStand;
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin-stand/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->form = new Event_Form_Admin_StandBanner(['model' => $this->_exhibStand]);
        if ($this->_request->isPost() && $this->form->isValid($this->_request->getPost())) {
            $this->_exhibStand->save();
            $this->_flash->success->addMessage($this->view->translate('label_form_save_success'));
            $this->_redirector->gotoRouteAndExit();
        }

        $this->view->form = $this->form;
    }

    public function deleteAction()
    {
        $dataBanner = $this->_exhibStand->getDataBanner();
        $bannerKey = $this->getParam('banner_key');
        $id_image = null;

        if (isset($dataBanner[$bannerKey])) {
            $id_image = $dataBanner[$bannerKey]['id_image'];
            unset($dataBanner[$bannerKey]);
        }

        if ($id_image) {
            $image = Image::find($id_image);
            if ($image) {
                Service_Image::delete($image);
            }
        }

        $this->_exhibStand->setDataBanners($dataBanner);
        $this->_exhibStand->save();

        $this->_flash->succes->addMessage($this->view->translate('label_delete_success'));
        $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand-banner');
    }

    private function checkExhibStandAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_exhibStand, 'ExhibStand NOT Exists (' . $this->_getParam('stand_hash') . ')');
        $this->forward403Unless($this->userAuth->hasAccess(null, $this->_exhibStand));
    }
}
