<?php

class MenuController extends Engine_Controller_Frontend
{
    /**
     * @var Menu
     */
    private $menu;

    public function indexAction()
    {
        $this->menu = Menu::findOneByUriFullAndIdBaseUser(
            $this->_getParam('uri_full'),
            $this->getSelectedBaseUser(),
            $this->getSelectedLanguage()
        );

        $this->forward404Unless($this->menu, 'Menu Not Found: uri_full: (' . $this->_getParam('uri_full') . '), id_base_user: ' . $this->getSelectedBaseUser()->getId());
        $this->forward404Unless($this->menu->is_active, 'Menu (' . $this->menu->getId() . ') is inactive');
        $this->forward404If($this->menu->isMenuTypeLink(), 'Menu (' . $this->menu->getId() . ') is link type');

        if ($this->menu->is_metatag) {
            $this->view->headTitle($this->menu->getMetatagTitle(), Zend_View_Helper_Placeholder_Container_Abstract::SET);
            $this->view->headMeta()->setName('description', $this->menu->getMetatagDesc());
            $this->view->headMeta()->setName('keywords', $this->menu->getMetatagKey());
        }

//        $this->initCurrentPlugin( $this->menu, ElementType::TYPE_MENU );

        $this->view->menu = $this->menu;
    }
}
