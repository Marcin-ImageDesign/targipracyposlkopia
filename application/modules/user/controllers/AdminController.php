<?php

class User_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \User_Form_User_Filter|mixed
     */
    public $filter;
    /**
     * @var User
     */
    private $user;

    /**
     * @var Doctrine_Collection
     */
    private $userList;

    /**
     * @var User_Form_User_Edit|User_Form_User_New
     */
    private $formUser;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_admin-list',
            'url' => $this->view->url([], 'admin_user'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->hasEventModule = (array_key_exists('event', $this->modules_config));
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('page-title_users-list'));
        $selectedOrder = $this->_session->user_order = $this->hasParam('order') ? trim($this->_getParam('order', 'created_at DESC')) : (isset($this->_session->user_order) ? $this->_session->user_order : 'created_at DESC');
        $search = ['email' => '', 'first_name' => '', 'last_name' => '', 'position' => '', 'is_active' => '', 'is_banned' => '', 'base_user' => '', 'company' => ''];

        if ($this->hasParam('clear')) {
            $this->_session->__unset('user_filter');
        } elseif (isset($search)) {
            $this->_session->user_filter = new stdClass();
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->user_filter->{$key} = $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->user_filter->{$key}) ? $this->_session->user_filter->{$key} : $value);
            }
        }

        $this->filter = new User_Form_User_Filter();
        $this->filter->populate($search);

        $sort = $this->_getParam('created_at DESC');

        $userQuery = Doctrine_Query::create()
            ->select('u.*')
            ->from('User u')
            ->orderBy('u.' . $selectedOrder)
        ;

        if (!$this->getSelectedBaseUser()->isSuperAdmin()) {
            $userQuery->addWhere('u.id_base_user = ?', $this->getSelectedBaseUser()->getId());
        }

        if (!empty($search['email'])) {
            $userQuery->addWhere('u.email LIKE ?', '%' . $search['email'] . '%');
        }

        if (!empty($search['first_name'])) {
            $userQuery->addWhere('u.first_name LIKE ?', '%' . $search['first_name'] . '%');
        }

        if (!empty($search['last_name'])) {
            $userQuery->addWhere('u.last_name LIKE ?', '%' . $search['last_name'] . '%');
        }

        if (!empty($search['position'])) {
            $userQuery->addWhere('u.id_user_role = ?', $search['position']);
        }

        if (mb_strlen($search['is_active']) > 0) {
            $userQuery->addWhere('u.is_active = ?', $search['is_active']);
        }

        if (mb_strlen($search['is_banned']) > 0) {
            $userQuery->addWhere('u.is_banned = ?', $search['is_banned']);
        }

        if (!empty($search['base_user']) && $this->getSelectedBaseUser()->isSuperAdmin()) {
            $userQuery->addWhere('u.id_base_user = ?', $search['base_user']);
        }

        if (!empty($search['company'])) {
            $userQuery->addWhere(' u.company = ? ', $search['company']);
        }

        if (!$this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $usersInEvents = Doctrine_Query::create()
                ->from('EventHasUser ehs')
                ->leftJoin('ehs.Event e')
                ->where('e.id_user_created = ?', $this->userAuth->getId())
                ->andWhere('e.id_base_user = ?', $this->getSelectedBaseUser()->getId())
                ->execute()
            ;
            $ownEventsPartipiciants = [];
            foreach ($usersInEvents as $eventHasUser) {
                $ownEventsPartipiciants[] = $eventHasUser->User->getId();
            }

            $userQuery->andWhere('(( u.id_user = ? OR u.id_user_created = ? ) OR u.id_user IN (?))', [
                $this->userAuth->getId(),
                $this->userAuth->getId(),
                implode(',', $ownEventsPartipiciants),
            ]);
        }

        $pager = new Doctrine_Pager($userQuery, $this->_getParam('page', 1), 20);
        $userList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->userList = $userList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;
    }

    public function statusAction()
    {
        $this->user = User::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->user, 'User Not Found (' . $this->_getParam('hash') . ')');

        $this->user->is_active = !$this->user->is_active;
        $this->user->save();

        $this->_flash->succes->addMessage($this->view->translate('cms_message_save_success'));
        $this->_redirector->gotoRouteAndExit([], 'admin_user');
    }

    public function newAction()
    {
        $this->user = new User();
        $this->user->BaseUser = $this->getSelectedBaseUser();
        $this->user->id_user_role = UserRole::ROLE_USER;
        $this->user->UserCreated = $this->userAuth;
        $this->user->hash = $this->engineUtils->getHash();
        $this->user->is_active = true;

        $this->formUser = new User_Form_User([
            'user' => $this->user,
        ]);

        //walidacja i wywowłanie formularza
        $this->formUser();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_user_new'));
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_admin-new',
        ];
    }

    public function editAction()
    {
        $this->user = User::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->user, 'User Not Found hash: (' . $this->_getParam('hash') . ')');

        $this->formUser = new User_Form_User_Edit([
            'user' => $this->user,
        ]);

        $this->formUser();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_user_edit'));
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_admin-edit',
        ];
    }

    public function deleteAction()
    {
        $this->user = User::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->user, 'Newsletter Not Found hash: (' . $this->_getParam('hash') . ')');
//        if(!$this->getSelectedBaseUser()->isSuperAdmin()){
//            $this->forward403Unless( $this->getSelectedBaseUser()->hasAccess( $this->user ), '403' );
//        }
//
//        $canEdit = $this->userAuthCanEdit($this->user);
//        $this->forward403Unless($canEdit, 'UserAuth ('.$this->userAuth->getId().') no access to delete User ('.$this->user->getId().')');

        $this->user->delete();
        $this->_flash->succes->addMessage($this->view->translate('cms_message_delete_success'));
        $this->_redirector->gotoRouteAndExit([], 'admin_user');
    }

    public function newSimplifiedAction()
    {
        if (!$this->getSelectedEvent()) {
            $url = $this->view->url([], 'admin_select-event') . '?return=' . $this->view->url();
            $this->_redirector->gotoUrlAndExit($url);
        }

        // nowy user
        $this->user = new User();
        $this->user->BaseUser = $this->getSelectedBaseUser();
        $this->user->UserCreated = $this->userAuth;
        $this->user->hash = $this->engineUtils->getHash();
        $this->user->is_active = true;
        $this->user->id_user_role = UserRole::ROLE_EXHIBITOR; // wystawca

        // nowe stoisko
        $exhibitStand = new ExhibStand();
        $exhibitStand->Event = $this->getSelectedEvent();
        $exhibitStand->BaseUser = $this->getSelectedBaseUser();
        $exhibitStand->hash = $this->engineUtils->getHash();
        $exhibitStand->id_exhib_stand_participation = ExhibStandType::STANDARD;
        $exhibitStand->id_stand_level = 1;
        $exhibitStand->is_owner_view = false;
        $exhibitStand->is_active = true;

        // nowe powiazanie user -> event
        $exhibParticipation = new ExhibParticipation();
        $exhibParticipation->hash = $this->engineUtils->getHash();
        $exhibParticipation->BaseUser = $this->getSelectedBaseUser();
        $exhibParticipation->Event = $this->getSelectedEvent();

        // nowe powiazanie stand -> event
        $exhibStandParticipation = new ExhibStandParticipation();
        $exhibStandParticipation->hash = $this->engineUtils->getHash();

        $formUserSimplified = new User_Form_User_Simplified([
            'user' => $this->user,
            'exhibitStand' => $exhibitStand,
            'exhibParticipation' => $exhibParticipation,
            'exhibStandParticipation' => $exhibStandParticipation,
        ]);

        if ($this->_request->isPost() && $formUserSimplified->isValid($this->_request->getPost())) {
            // zapisanie wszystkiego za jednym razem :)
            $exhibStandParticipation->save();
            $this->_flash->succes->addMessage($this->view->translate('label_form_save_success'));
            $this->_redirector->gotoRouteAndExit([], 'admin_user_new_simplified');
        }

        $this->view->formUserSimplified = $formUserSimplified;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_new_user_exhibitor'));
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_admin-new',
        ];
    }

    private function formUser()
    {
        $this->_helper->viewRenderer('form-user');

        if ($this->_request->isPost() && $this->formUser->isValid($this->_request->getPost())) {
            if (!empty($_FILES['img']) && 0 === $_FILES['img']['error']) {
                $image = Service_Image::createImage(
                    $this->user,
                    [
                        'type' => $_FILES['img']['type'],
                        'name' => $_FILES['img']['name'],
                        'source' => $_FILES['img']['tmp_name'], ]
                );

                $this->user->setAvatarImage(true);
                $this->user->setIdImage($image->getId());
            }
            $this->user->save();
            $this->_flash->succes->addMessage($this->view->translate('cms_message_save_success'));
            $this->_redirector->gotoRouteAndExit(['hash' => $this->user->hash], 'admin_user_edit');
        }

        $this->view->formUser = $this->formUser;
        $this->view->user = $this->user;
        $this->view->placeholder('section-class')->set('tpl-form');
    }
}
