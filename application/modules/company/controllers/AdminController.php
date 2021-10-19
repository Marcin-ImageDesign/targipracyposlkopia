<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminController.
 *
 * @author marcin
 */
class Company_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \Company_Form_Filter|mixed
     */
    public $filter;
    /**
     * @var Company
     */
    protected $company;
    /**
     * @var Company_Form_Company_Element
     */
    protected $formCompany;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_company'),
            'url' => $this->view->url([], 'company_admin'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

//        if ( $this->_helper->layout->isEnabled() ) {
//            $this->view->renderToPlaceholder('admin/_contentNav.phtml','nav-left');
//        }
    }

    public function indexAction()
    {
        $selectedOrder = $this->_session->company_order = $this->_hasParam('order') ? trim($this->_getParam('order', 'name ASC')) : (isset($this->_session->company_order) ? $this->_session->company_order : 'name ASC');

        $search = ['name' => '', 'krs' => '', 'regon' => '', 'nip' => '', 'representative' => '', 'is_active' => ''];
        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('company_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->company_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->company_filter->{$key}) ? $this->_session->company_filter->{$key} : $value);
            }
        }

        $this->filter = new Company_Form_Filter();
        $this->filter->populate($search);

        $companiesQuery = Doctrine_Query::create()
            ->from('Company c')
            ->where('c.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->orderBy($selectedOrder)
        ;
        if (!$this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $companiesQuery->addWhere('c.id_user_created = ?', $this->userAuth->getId());
        }

        if (!empty($search['name'])) {
            $companiesQuery->addWhere('c.name LIKE ?', '%' . $search['name'] . '%');
        }

        if (!empty($search['krs'])) {
            $companiesQuery->addWhere('c.krs LIKE ?', '%' . $search['krs'] . '%');
        }

        if (!empty($search['nip'])) {
            $companiesQuery->addWhere('c.nip LIKE ?', '%' . $search['nip'] . '%');
        }
        if (!empty($search['representative'])) {
            $companiesQuery->addWhere('c.representative LIKE ?', '%' . $search['representative'] . '%');
        }
        if (!empty($search['is_active'])) {
            $companiesQuery->addWhere('c.is_active = ?', $search['representative']);
        }

        $this->view->companiesList = $companiesQuery->execute();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_company-list'));

        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
        $pager = new Doctrine_Pager($companiesQuery, $this->_getParam('page', 1), 20);
        $companiesList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->companiesList = $companiesList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;
    }

    public function newAction()
    {
        $this->company = new Company();
        $this->company->hash = Engine_Utils::getInstance()->getHash();
        $this->company->UserCreated = $this->userAuth;
        $this->company->BaseUser = $this->getSelectedBaseUser();

        $this->formCompany();

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_company_new'));
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_company-new'),
            'url' => $this->view->url(),
        ];
    }

    public function editAction()
    {
        $this->company = Company::findOneByHash($this->_getParam('hash'));

        $this->forward404Unless($this->company, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->company),
            [$this->getSelectedBaseUser()->getId(), $this->company->getHash()]
        );

        $this->formCompany();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_company_edit'));
        $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_company-edit'),
            'url' => $this->view->url(),
        ];
    }

    public function deleteAction()
    {
        $this->company = Company::findOneByHash($this->_getParam('hash'));

        $this->forward404Unless($this->company, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->company),
            [$this->getSelectedBaseUser()->getId(), $this->company->getHash()]
        );

        $this->company->delete();
        $this->_flash->success->addMessage('Company deleted');

        $this->_redirector->gotoRouteAndExit([], 'company_admin');
    }

    public function statusAction()
    {
        $this->company = Company::findOneByHash($this->_getParam('hash'));

        $this->forward404Unless($this->company, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->company),
            [$this->getSelectedBaseUser()->getId(), $this->company->getHash()]
        );

        $this->company->is_active = !$this->company->is_active;

        $this->company->save();
        $this->_flash->success->addMessage('cms-page-title_company_status-change');

        $this->_redirector->gotoRouteAndExit([], 'company_admin');
    }

    //usuwanie zdjęcia z podstrony edycji
    public function deleteImgAction()
    {
        //pobranie obiektu na podstawie pola hash
        $this->company = Company::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->company, 'Event Not Found hash: (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->company),
            [$this->getSelectedBaseUser()->getId(), $this->company->getId()]
        );
        //Usuwanie zdjęcia z serwera
        $this->company->deleteImage();
        //zapis do bazy
        $this->company->save();
        $this->_flash->success->addMessage('Item has been deleted');
        //przekierowanie na podstronę edycji
        $this->_redirector->gotoRouteAndExit(['hash' => $this->company->getHash()], 'cms-page-title_company_admin-edit');
    }

    private function formCompany()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('form-company');
        //pobranie formularza dla pojedyńczego elementu
        $this->formCompany = $this->getFormCompany();
//        var_dump($this->formEvent->getElementsAndSubFormsOrdered());
        //        $pluginClass = $this->initCurrentPlugin( $this->company, $this->formCompany );
        //        die(print_r($this->_getAllParams()));
        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->formCompany->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->companyGetRequest();
            //zapisa adresu i branż
            //                $this->runPluginMethod($pluginClass, 'preSave');
            //zapis zmian do bazy
            $this->company->save();
            //Tworzenie informacji zwrotnej
            $this->_flash->success->addMessage($this->view->translate('message_success_save'));
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->company->hash], 'company_admin_edit');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->form = $this->formCompany;
        $this->view->company = $this->company;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function getFormCompany()
    {
        //pobranie formualrza
        $formCompany = new Company_Form_Company_Element(['model' => $this->company]);

        //Tworzenie tablicy z elemntów formularza
        $formCompany->populate($this->company->getArrayToForm());

        return $formCompany;
    }

    private function companyGetRequest()
    {
        $this->company->krs = $this->formCompany->krs->getValue();
//        die($this->formCompany->main->nip->getValue());
        $this->company->nip = $this->formCompany->nip->getValue();
        $this->company->regon = $this->formCompany->regon->getValue();
        $this->company->name = $this->formCompany->name->getValue();
        $this->company->is_active = $this->formCompany->is_active->getValue();
        $this->company->setEmail($this->formCompany->email->getValue());
        $this->company->setWww($this->formCompany->www->getValue());

        $information = '';
//        $upload = new Zend_File_Transfer_Adapter_Http();
//        $files = $upload->getFileInfo();
//
//
//        if( !empty( $files ) && isset( $files['img'] ) && $files['img']['error'] != 4 ){
//            $this->company->deleteImage();
//
//            $file_tmp = explode('.', $_FILES['img']['name']);
//            $tmp_name = $files['img']['tmp_name'];
//            // var_dump($file_tmp);exit;
////                $imageInfo = getimagesize($tmp_name);
//
//            $file_name = $this->company->getHash() . '.' . $file_tmp[count($file_tmp) - 1];
//            $this->company->img = $this->engineUtils->getFileExt( $files['img']['name'] );
//            $this->formCompany->img->addFilter(new Zend_Filter_File_Rename(array('target' => $file_name)));
//
//            $image = Engine_Image::factory();
//            $engineVariable = Engine_Variable::getInstance();
//            $width = $engineVariable->getVariable(Variable::COMPANY_IMAGE_THUMB_WIDTH,
//                                                    $this->getSelectedBaseUser()->getId()
//                    );
//            $height = $engineVariable->getVariable(Variable::COMPANY_IMAGE_THUMB_HEIGHT,
//                                                    $this->getSelectedBaseUser()->getId()
//                    );
//            $image->open( $tmp_name );
//            $image->cropAndResizeFromCenter( $width , $height );
//            $image->save( $this->company->getRelativeImageThumb(true,  $this->company->img) );
////                $this->eventSpeakers->setImg($file_name);
//        }
    }
}
