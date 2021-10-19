<?php
/**
 * Created by PhpStorm.
 * User: marek
 * Date: 25.10.13
 * Time: 11:08.
 */
class Shop_AdminLocationController extends Engine_Controller_Admin
{
    /**
     * @var ShopLocation
     */
    protected $_shopLocation;

    /**
     * @var Doctrine_Collection
     */
    protected $_shopLocationList;

    /**
     * @var Shop_Form_Location_Element
     */
    protected $_form;

    public function indexAction()
    {
        $shopLocationQuery = Doctrine_Query::create()
            ->from('ShopLocation sl')
        ;

        if (!$this->userAuth->isAdmin()) {
            $shopLocationQuery->where('sl.id_event = ?', $this->getSelectedEvent()->getId());
        }

        $this->_shopLocationList = $shopLocationQuery->execute();

        $this->view->shopLocationList = $this->_shopLocationList;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_h1_admin-location_shop'));
    }

    public function newAction()
    {
        $this->_shopLocation = new ShopLocation();
        $this->_shopLocation->Event = $this->getSelectedEvent();
        $this->_shopLocation->hash = $this->engineUtils->getHash();

        $this->_formShopLocation();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_h1_admin-location_shop_new'));
    }

    public function editAction()
    {
        $this->_shopLocation = ShopLocation::findOneByHash($this->getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_shopLocation, 'Shop Location NOT Exists (' . $this->_getParam('hash') . ')');

        $this->_formShopLocation();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_h1_admin-location_shop_edit'));
    }

    public function deleteAction()
    {
        $this->_shopLocation = ShopLocation::findOneByHash($this->getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_shopLocation, 'Shop Location NOT Exists (' . $this->_getParam('hash') . ')');

        $this->_shopLocation->delete();

        $this->_flash->succes->addMessage('Shop location has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'shop_admin-location');
    }

    protected function _formShopLocation()
    {
        $this->_helper->viewRenderer('admin/form', null, true);
        $this->_form = new Shop_Form_Location_Element(['model' => $this->_shopLocation]);

        if ($this->_request->isPost() && $this->_form->isValid($this->_request->getPost())) {
            $this->_shopLocation->save();

            $this->_flash->success->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit([], 'shop_admin-location');
        }

        $this->view->form = $this->_form;
    }
}
