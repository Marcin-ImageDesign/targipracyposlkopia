<?php

/**
 * Description of AdminController.
 *
 * @author marcin
 */
class Brand_AdminController extends Engine_Controller_Admin
{
    /**
     * @var Brand
     *
     * * */
    protected $_brand;

    /**
     * @var Brand_Form_Element
     */
    protected $brandForm;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_brand',
            'url' => $this->view->url([], 'brand_admin'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('_contentNav.phtml', 'nav-left');
        }
    }

//
//    protected function getBrandFromRequest()
//    {
//        $id = $this->_getParam('id');
//        if(!$id) {
//            $brand = new Brand();
//        } else {
//            $brand = Brand::find($id, $this->getSelectedLanguage());
//            $this->forward404If($brand == false, 'Brand not exists,id(' . $id . ')');
//        }
//        $this->brand = $brand;
//        return $brand;
//    }
//
//    protected function getBrandForm(Brand $brand)
//    {
//        $brandForm = new Brand_Form_Element($brand);
//        $brandForm->populate($brand->getArrayToForm());
//        $this->view->placeholder('section-class')->set('tpl-form');
//        $this->brandForm = $brandForm;
//        return $brandForm;
//    }

    public function indexAction()
    {
        $brandsList = Doctrine_Query::create()
            ->from('Brand b')
            ->where('b.id_brand_parent IS NULL')
            ->execute()
        ;

        $this->view->brandsList = $brandsList;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Brand list'));
    }

    public function newAction()
    {
        $this->_brand = new Brand();
        $this->_brand->hash = Engine_Utils::_()->getHash();
        $this->_brand->is_active = true;

        if ($this->hasParam('parent')) {
            $this->_brand->id_brand_parent = $this->getParam('parent');
        }

        $this->_formBrand();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('page-title_brand-admin_new'));
    }

    public function editAction()
    {
        $this->_brand = Brand::find($this->getParam('id'));
        $this->forward404Unless($this->_brand);

        $this->_formBrand();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('page-title_brand-admin_edit'));
    }

//    protected function getBrandModelFromRequest()
//    {
//        if(!$this->getSelectedBaseUser()->isDefaultLanguage($this->getSelectedLanguage()) && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
//            if(!isset($this->brand->BrandLanguageOne)) {
//                $this->brand->BrandLanguageOne = new BrandLanguage();
//                $this->brand->BrandLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
//            }
//        }
//        if($this->_request->isPost()) {
//            if($this->brandForm->isValid($this->_getAllParams())) {
//                if($this->brand->isNew()) {
//                    $this->brand->hash = Engine_Utils::getInstance()->getHash();
//                }
//                if($this->brandForm->parent->getValue()) {
//                    $brandParent = Brand::find($this->brandForm->parent->getValue());
//                    $this->forward404If($brandParent == false, 'Brand parent not exists,id:' .
//                    $this->brandForm->parent->getValue());
//                    $this->brand->BrandParent = $brandParent;
//                } else {
//                    $this->brand->BrandParent = null;
//                }
//
//                $this->brand->setIsActive($this->brandForm->is_active->getValue());
//                $this->brand->setName($this->brandForm->name->getValue());
//                $this->brand->save();
//                $this->_flash->succes->addMessage('Save successfully completed');
//                $this->_redirector->gotoRouteAndExit(array('id' => $this->brand->getId()), 'brand_admin_edit');
//            }
//        }
//    }

//    public function newAction()
//    {
    ////        $this->setSelectedLanguage();
//        $this->view->brandForm = $this->getBrandForm($this->getBrandFromRequest());
//
//        $this->getBrandModelFromRequest();
//
//        if($this->_getParam('parent')) {
//            $parent = $this->_getParam('parent');
//            $this->view->brandForm->populate(array('parent' => $parent));
//        }
//        $this->view->placeholder('headling_1_content')->set($this->view->translate('Add brand'));
//        $this->_breadcrumb[] = array(
//            'label' => 'breadcrumb_cms_brand-new',
//            'url' => $this->view->url()
//        );
//    }
//
//    public function editAction()
//    {
//        $this->view->brandForm = $this->getBrandForm($this->getBrandFromRequest());
//        $this->getBrandModelFromRequest();
//        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit brand'));
//        $this->_breadcrumb[] = array(
//            'label' => 'breadcrumb_cms_brand-edit',
//            'url' => $this->view->url()
//        );
//    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');

        if (!$id) {
            $this->_flash->error->addMessage('Brand not found');
            $this->_redirector->gotoRouteAndExit([], 'brand_admin');
        }
        $brand = Brand::find($id);

        $this->forward404If(false === $brand, 'Brand not exists,id(' . $id . ')');
        //nie wiem czemu ale dodanie info do messangera przed zmianami na modelu powoduje że się one nie pokazują
        $this->_flash->succes->addMessage('Brand removed');

        $brand->delete();

        $this->_redirector->gotoRouteAndExit([], 'brand_admin');
    }

    public function statusAction()
    {
        $id = $this->_getParam('id');

        if (!$id) {
            $this->_flash->error->addMessage('Brand not found');
            $this->_redirector->gotoRouteAndExit([], 'brand_admin');
        }
        $brand = Brand::find($id);

        $this->forward404If(false === $brand, 'Brand not exists,id(' . $id . ')');

        $brand->is_active = (!(bool) $brand->isActive());
        $brand->save();

        $this->_flash->success->addMessage('Status changed');
        $this->_redirector->gotoRouteAndExit([], 'brand_admin');
    }

    private function _formBrand()
    {
        $form = new Brand_Form_Element(['model' => $this->_brand]);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $this->_brand->save();

            $this->_flash->success->addMessage($this->view->translate('label_form_save_success'));
            $this->_redirector->gotoRouteAndExit(['id' => $this->_brand->getId()], 'brand_admin_edit');
        }

        $this->view->form = $form;
        $this->_helper->viewRenderer('form');
    }
}
