<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminBrandController.
 *
 * @author marcin
 */
class Company_AdminBrandController extends Engine_Controller_Admin
{
    /**
     * @var Company_Form_BrandHasCompany_Element
     */
    protected $formBrandHasCompany;
    /**
     * @var BrandHasCompany
     */
    protected $brandHasCompany;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
    }

    public function newAction()
    {
    }

    public function editAction()
    {
    }

    public function deleteAction()
    {
    }
}
