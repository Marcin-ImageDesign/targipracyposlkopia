<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminUserController.
 *
 * @author marcin
 */
class Company_AdminUserController extends Engine_Controller_Admin
{
    /**
     * @var \Company_Form_CompanyHasUser_Filter|mixed
     */
    public $filter;
    /**
     * @var CompanyHasUser
     */
    protected $companyHasUser;
    /**
     * @var Company_Form_CompanyHasUser_Element
     */
    protected $companyHasUserForm;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin-user/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $selectedOrder = $this->_session->event_order = $this->_hasParam('order') ? trim($this->_getParam('order', 'created_at DESC')) : (isset($this->_session->event_order) ? $this->_session->event_order : 'created_at DESC');

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Employeement list'));

        $search = ['user' => '', 'company' => '', 'position' => '', 'is_active' => ''];
        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('companyhasuser_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->companyhasuser_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->companyhasuser_filter->{$key}) ? $this->_session->companyhasuser_filter->{$key} : $value);
            }
        }

        $search['hash'] = $this->_getParam('hash', '');

        $companyHasUserQuery = Doctrine_Query::create()
            ->from('CompanyHasUser chu')
            ->where('chu.id_base_user = ?', $this->getSelectedBaseUser()->getId())
        ;

        if (!$this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $companyHasUserQuery->addWhere('chu.id_user_created = ?', $this->userAuth->getId());
        }

        $this->filter = new Company_Form_CompanyHasUser_Filter();
        $this->filter->populate($search);

        if (!empty($search['user'])) {
            $user = User::findOneByHash($search['user']);
            $companyHasUserQuery->addWhere('chu.id_user = ?', $user->getId());
        }

        if (!empty($search['company'])) {
            $company = Company::findOneByHash($search['company']);
            $companyHasUserQuery->addWhere('chu.id_company = ?', $company->getId());
        }

        if (!empty($search['is_active'])) {
            $companyHasUserQuery->addWhere('chu.is_active = ?', $search['is_active']);
        }

        if (!empty($search['position'])) {
            $companyHasUserQuery->addWhere('chu.id_company_position = ?', $search['position']);
        }

        if (!empty($search['hash']) && empty($search['company'])) {
            $company = Company::findOneByHash($search['hash']);
            $companyHasUserQuery->addWhere('chu.id_company = ?', $company->getId());
            $this->view->placeholder('headling_1_content')->set('"' . $company->getName() . '" - ' . $this->view->translate('Employeement list'));
        }

        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
        $pager = new Doctrine_Pager($companyHasUserQuery, $this->_getParam('page', 1), 20);
        $companyHasUserList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->companyHasUserList = $companyHasUserList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;
    }

    public function newAction()
    {
        $this->companyHasUser = new CompanyHasUser();
        $this->companyHasUser->UserCreated = $this->userAuth;
        $this->companyHasUser->hash = Engine_Utils::getInstance()->getHash();
        $this->companyHasUser->BaseUser = $this->getSelectedBaseUser();

        $this->formCompanyHasUser();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Add employeement'));
    }

    public function editAction()
    {
        $this->companyHasUser = CompanyHasUser::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->companyHasUser, 'CompanyHasUser NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->companyHasUser),
            [$this->getSelectedBaseUser()->getId(), $this->companyHasUser->getHash()]
        );

        $this->formCompanyHasUser();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit employeement'));
    }

    public function deleteAction()
    {
        $this->companyHasUser = CompanyHasUser::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->companyHasUser, 'CompanyHasUser NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->companyHasUser),
            [$this->getSelectedBaseUser()->getId(), $this->companyHasUser->getHash()]
        );

        $this->companyHasUser->delete();

        $this->_flash->success->addMessage('Employeement deleted');

        $this->_redirector->gotoRouteAndExit([], 'company_admin-user');
    }

    public function statusAction()
    {
        $this->companyHasUser = CompanyHasUser::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->companyHasUser, 'CompanyHasUser NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->companyHasUser),
            [$this->getSelectedBaseUser()->getId(), $this->companyHasUser->getHash()]
        );
        $this->companyHasUser->is_active = !$this->companyHasUser->is_active;

        $this->_flash->success->addMessage('Employeement status changed');

        $this->_redirector->gotoRouteAndExit([], 'company_admin-user');
    }

    private function formCompanyHasUser()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('form');
        //pobranie formularza dla pojedyńczego elementu;
        $this->companyHasUserForm = $this->getFormCompanyHasUser();
//        var_dump($this->formEvent->getElementsAndSubFormsOrdered());
        //        die(print_r($this->_getAllParams()));
        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->companyHasUserForm->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->companyHasUserRequest();
            //zapis zmian do bazy
            $this->companyHasUser->save();
            //Tworzenie informacji zwrotnej
            $this->_flash->success->addMessage('Save successfully completed');
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->companyHasUser->hash], 'company_admin-user_edit');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->form = $this->companyHasUserForm;
        $this->view->companyHasUser = $this->companyHasUser;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function getFormCompanyHasUser()
    {
        //pobranie formualrza
        $formCompany = new Company_Form_CompanyHasUser_Element($this->companyHasUser);

        //Tworzenie tablicy z elemntów formularza
        $formCompany->populate($this->companyHasUser->getArrayToForm());

        return $formCompany;
    }

    private function companyHasUserRequest()
    {
        $company = $this->companyHasUserForm->header->company->getValue();
        $companyObject = Company::findOneByHash($company);

        $this->forward404Unless($companyObject, 'Company NOT Exists (' . $this->companyHasUserForm->header->company->getValue() . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($companyObject),
            [$this->getSelectedBaseUser()->getId(), $companyObject->getHash()]
        );

        $userHash = $this->companyHasUserForm->header->user->getValue();
        $userObject = User::findOneByHash($userHash);

        $this->forward404Unless($userObject, 'User NOT Exists (' . $this->companyHasUserForm->header->user->getValue() . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($userObject),
            [$this->getSelectedBaseUser()->getId(), $userObject->getHash()]
        );

        $companyPositionId = $this->companyHasUserForm->main->position->getValue();
        $companyPosition = CompanyPosition::find($companyPositionId);
        $this->forward404Unless($companyPosition, 'User NOT Exists (' . $this->companyHasUserForm->main->position->getValue() . ')');

        $this->companyHasUser->CompanyPosition = $companyPosition;
        $this->companyHasUser->Company = $companyObject;
        $this->companyHasUser->User = $userObject;
    }
}
