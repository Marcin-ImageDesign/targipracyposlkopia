<?php

class Event_AdminUserController extends Engine_Controller_Admin
{
    /**
     * @var \Doctrine_Query|mixed
     */
    public $eventHasUserQuery;
    public $eventListQuery;
    /**
     * @var Doctrine_Collection
     */
    private $eventHasUserList;

    /**
     * @var EventHasUser
     */
    private $eventHasUser;

    /**
     * @var Event_Form_User_EventHasUser_Element
     */
    private $formEventHasUser;
    /**
     * @var Event_Admin_User_Filter
     */
    private $filter;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $session_order_key = 'event_has_user_order';
        $session_search_key = 'event_has_user_search';
        $selectedOrder = $this->_session->{$session_order_key} = $this->_hasParam('order') ? trim($this->_getParam('order', 'ehu.created_at DESC')) : (isset($this->_session->{$session_order_key}) ? $this->_session->{$session_order_key} : 'ehu.created_at DESC');

        $search = ['user' => '', 'event' => '', 'is_confirm' => '', 'hash' => ''];
        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset($session_search_key);
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->{$session_search_key}->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->{$session_search_key}->{$key}) ? $this->_session->{$session_search_key}->{$key} : $value);
            }
        }

        // deklaracja formularza event/form/Filter.php
        $this->filter = new Event_Form_User_Filter();
        $this->filter->populate($search);

        $this->eventHasUserQuery = Doctrine_Query::create()
            ->from('EventHasUser ehu')
            ->innerJoin('ehu.User u')
            ->innerJoin('ehu.Event e')
            ->where(
                'e.id_base_user = ? AND u.id_base_user = ?',
                [$this->getSelectedBaseUser()->getId(), $this->getSelectedBaseUser()->getId()]
            )
            ->orderBy($selectedOrder)
        ;

        // jeśli zbyt nieskie uprawnienia, wyświetlamy tylko eventy których jestem właścicielem
        if (!$this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $this->eventHasUserQuery->addWhere('e.id_user_created = ?', $this->userAuth->getId());
        }

        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
            $this->eventListQuery->leftJoin('e.EventLanguageOne elo WITH elo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
        }

        if (!empty($search['hash'])) {
            $this->eventHasUserQuery->addWhere('u.hash = ?', $search['hash']);
        }

        // dodanie warunków z filtra
        if (!empty($search['user'])) {
            $this->eventHasUserQuery->addWhere("CONCAT(u.first_name, ' ', u.last_name, ' ', u.last_name, ' ' , u.first_name) LIKE ?", '%' . $search['user'] . '%');
        }

        if (!empty($search['event'])) {
            $this->eventHasUserQuery->addWhere('e.title LIKE ?', '%' . $search['event'] . '%');
        }

        if (mb_strlen($search['is_confirm']) > 0) {
            $this->eventHasUserQuery->addWhere('ehu.is_confirm = ?', $search['is_confirm']);
        }

        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
        $pager = new Doctrine_Pager($this->eventHasUserQuery, $this->_getParam('page', 1), 20);
        $this->eventHasUserList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->eventHasUserList = $this->eventHasUserList;

        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));

        $this->view->filter = $this->filter;

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set('Lista zgłoszeń');
    }

    public function confirmAction()
    {
        $this->eventHasUser = EventHasUser::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->eventHasUser, 'EventHasUset NOT Exists hash(' . $this->_getParam('hash') . ')');
        $this->forward403Unless($this->getSelectedBaseUser()->hasAccess($this->eventHasUser->User));

        //sprawdzenie czy user ma prawa dostępu
        $this->forward403Unless(
            $this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL, $this->eventHasUser->Event)
        );

        $value = !$this->eventHasUser->is_confirm;
        $changeStatus = true;
        if ($this->eventHasUser->Event->getUsersLimit() && $value) {
            $usersLimit = $this->eventHasUser->Event->getUsersLimit();
            $assignedUsers = $this->eventHasUser->Event->getAsignedUsers();
            if (($assignedUsers + 1) > $usersLimit) {
                $changeStatus = false;
                $this->_flash->error->addMessage('Cant add more users to event');
            }
        }
        if ($changeStatus) {
            $this->eventHasUser->is_confirm = $value;
            $this->eventHasUser->save();
        }

        $return = $this->_getParam('return');
        if (!empty($return)) {
            $this->_redirector->gotoUrlAndExit($return);
        } else {
            $this->_redirector->gotoRouteAndExit([], 'event_admin-user');
        }
    }

    public function deleteAction()
    {
        $this->eventHasUser = EventHasUser::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->eventHasUser, 'EventHasUset NOT Exists hash(' . $this->_getParam('hash') . ')');
        $this->forward403Unless($this->getSelectedBaseUser()->hasAccess($this->eventHasUser->User));

        //sprawdzenie czy user ma prawa dostępu
        $this->forward403Unless(
            $this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL, $this->eventHasUser->Event)
        );

        $this->eventHasUser->delete();
        $this->_flash->success->addMessage('Item has been deleted');

        $return = $this->_getParam('return');
        if (!empty($return)) {
            $this->_redirector->gotoUrlAndExit($return);
        } else {
            $this->_redirector->gotoRouteAndExit([], 'event_admin-user');
        }
    }

    public function newEventHasUserAction()
    {
        $this->forward403Unless($this->userAuth->hasAccess(AuthPermission::EVENT_HAS_USER_NEW), 'Lack of privilages');
        $this->eventHasUser = new EventHasUser();

        $this->setSelectedLanguage();

        $this->formEventHasUser();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('New participation'));
    }

    public function editEventHasUserAction()
    {
        $this->forward403Unless($this->userAuth->hasAccess(AuthPermission::EVENT_HAS_USER_EDIT), 'Lack of privilages');

        $hash = $this->_getParam('hash', '');

        $this->eventHasUser = EventHasUser::findOneByHash($hash);
        $this->forward404Unless($this->eventHasUser, 'Event NOT Exists (' . $this->_getParam('hash') . ')');

        $this->setSelectedLanguage();

        $this->formEventHasUser();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit participation'));
    }

//    public function indexAction()
//    {
//        /* Ustawienie sortowania listy */
//        $selectedOrder = $this->_session->event_order = $this->_hasParam( 'order' ) ? trim( $this->_getParam( 'order', 'created_at DESC' ) ) : ( isset( $this->_session->event_order ) ? $this->_session->event_order : 'created_at DESC' );
//        //Deklaracja tablicy z filtrami
//        $search = array('title' => '', 'is_active' => '', 'is_archive' => '');
//
//        if ( $this->_hasParam( 'clear' ) ) {
//            //czyszczenie filtra
//            $this->_session->__unset( 'event_filter' );
//        } else {
//            //Ustawienie filtrów z wyszukiwarki
//            foreach ($search as $key => $value)
//            {
//                $search[$key] = $this->_session->event_filter->$key = $this->_hasParam( $key ) ? trim( $this->_getParam( $key, $value ) ) : ( isset( $this->_session->event_filter->$key ) ? $this->_session->event_filter->$key : $value );
//            }
//        }
//        // deklaracja formularza event/form/Filter.php
//        $this->filter = new Event_Form_Filter();
//        $this->filter->populate( $search );
//
//        //tworzenie zaptania zwracającego listę
//        $this->eventListQuery = Doctrine_Query::create()
//        ->from( 'Event e' )
//        ->where( 'e.id_base_user = ?', $this->getSelectedBaseUser()->getId() )
//        ->orderBy( 'e.' . $selectedOrder );
//        //sprawdzenie języka
//        if ( $this->getSelectedLanguage()->code !== Language::DEFAULT_LANGUAGE_CODE && isset( $this->getSelectedLanguage()->BaseUserLanguageOne ) ) {
//            $this->eventListQuery->leftJoin( 'e.EventLanguageOne elo WITH elo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId() );
//        }
//        //sprawdzenie tytułu z filtra
//        if ( !empty( $search['title'] ) ) {
//            $this->eventListQuery->addWhere( 'e.title LIKE ?', '%' . $search['title'] . '%' );
//        }
//        //sprawdzenie statusu z filtra
//        if ( strlen( $search['is_active'] ) > 0 ) {
//            $this->eventListQuery->addWhere( 'e.is_active = ?', $search['is_active'] );
//        }
//        //sprawdzenie czy archiwalna
//        if ( strlen( $search['is_archive'] ) > 0 ) {
//            $this->eventListQuery->addWhere( 'e.is_archive = ?', $search['is_archive'] );
//        }
//
    ////        var_dump( $this->userAuth->hasAccess( AuthPermission::EVENT_ACCESS_TO_ALL ) );
    ////        var_dump( get_class_methods( $this->userAuth ) );
    ////        exit();
//
    ////        var_dump( $this->userAuth->hasAccessTest( AuthPermission::EVENT_ACCESS_TO_ALL ) );
//        if ( !$this->userAuth->hasAccess( AuthPermission::EVENT_ACCESS_TO_ALL ) ) {
//            $this->eventListQuery->addWhere( 'e.id_user_created = ?', $this->userAuth->getId() );
//        }
//
//
//        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
//        $pager = new Doctrine_Pager( $this->eventListQuery, $this->_getParam( 'page', 1 ), 20 );
//        $eventList = $pager->execute();
//        $pagerRange = new Doctrine_Pager_Range_Sliding( array('chunk' => 5), $pager );
//        $pages = $pagerRange->rangeAroundPage();
//
//        $this->view->pager = $pager;
//        $this->view->pages = $pages;
//        $this->view->pagerRange = $pagerRange;
//        $this->view->eventList = $eventList;
//        $this->view->selectedOrder = $selectedOrder;
//        $this->view->orderCol = trim( substr( $selectedOrder, 0, -4 ) );
//        $this->view->orderDir = trim( substr( $selectedOrder, -4 ) );
//        $this->view->filter = $this->filter;
//
//        //nagłówek podstrony
//        $this->view->placeholder( 'headling_1_content' )->set( 'Zarządzanie eventami' );
//    }

    private function formEventHasUser()
    {
        $this->_helper->viewRenderer('form-event-has-user');
        //pobranie formularza dla pojedyńczego elementu
        $this->formEventHasUser = new Event_Form_User_EventHasUser_Element(
            $this->eventHasUser,
            $this->getSelectedLanguage(),
            []
        );

        if ($this->_request->isPost() && $this->formEventHasUser->isValid($this->getRequest()->getPost())) {
            $this->eventHasUser->id_event = $this->formEventHasUser->header->event->getValue();
            $this->eventHasUser->id_user = $this->formEventHasUser->header->user->getValue();
            $this->eventHasUser->is_confirm = (bool) $this->formEventHasUser->aside->is_confirm->getValue();
            if ($this->eventHasUser->isNew()) {
                $this->eventHasUser->hash = Engine_Utils::getInstance()->getHash();
            }
            $this->eventHasUser->save();
            $this->_flash->success->addMessage('Save successfully completed');
            $this->_redirector->gotoRouteAndExit(
                [
                    'hash' => $this->eventHasUser->hash,
                ],
                'event_admin-user_edit'
            );
        }

        $this->view->formEventHasUser = $this->formEventHasUser;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
    }
}
