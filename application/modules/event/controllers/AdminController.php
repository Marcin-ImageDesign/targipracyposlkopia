<?php

class Event_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Filter|mixed
     */
    public $filter;
    /**
     * @var \Doctrine_Query|mixed
     */
    public $eventListQuery;
    /**
     * @var Event
     */
    private $event;

    /**
     * @var Event_Form_Element
     */
    private $formEvent;

    /**
     * @var Doctrine_Collection
     */
    private $eventList;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event-list',
            'url' => $this->view->url([], 'admin_event'),
        ];
    }

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
        // Ustawienie sortowania listy
        $selectedOrder = $this->_session->event_order = $this->hasParam('order') ? trim($this->_getParam('order', 'created_at DESC')) : (isset($this->_session->event_order) ? $this->_session->event_order : 'created_at DESC');
        //Deklaracja tablicy z filtrami
        $search = ['title' => '', 'own' => '', 'is_active' => '', 'is_archive' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('event_filter');
        } else {
            $this->_session->event_filter = new stdClass();

            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->event_filter->{$key} =
                    $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->event_filter->{$key}) ? $this->_session->event_filter->{$key} : $value);
            }
        }

        // deklaracja formularza event/form/Filter.php
        $this->filter = new Event_Form_Filter();
        $this->filter->populate($search);

        //tworzenie zaptania zwracającego listę
        $this->eventListQuery = Doctrine_Query::create()
            ->from('Event e')
            ->leftJoin('e.Translations et WITH et.id_language = ?', Engine_I18n::getLangId())
        ;

        if ($this->userAuth) {
            $userInEvents = Doctrine_Query::create()
                ->from('EventHasUser ehu')
                ->where('ehu.id_user = ?', $this->userAuth->getId())
                ->addWhere('ehu.is_confirm = 1')
                ->execute()
            ;
        }

        if (!empty($search['own'])) {
            $tmpArray = [];
            foreach ($userInEvents as $eventsHasUser) {
                $tmpArray[] = $eventsHasUser->Event->getId();
            }
            $this->eventListQuery->whereIn('e.id_event ', $tmpArray);
            $this->eventListQuery->orWhere('e.id_user_created = ?', $this->userAuth->getId());
        }

        //jeśli zalogowany organizator pokazujemy tylko jego wydarzenia
        if (UserRole::ROLE_ORGANIZER == $this->userAuth->UserRole->getId()) {
            $this->eventListQuery->leftJoin('e.ExhibParticipation ep')
                ->addWhere('ep.id_exhib_participation_type = ? AND ep.id_user = ? AND ep.is_active = 1', [ExhibParticipationType::TYPE_ORGANIZER, $this->userAuth->getId()])
            ;
        }

        $this->eventListQuery->addWhere('e.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->orderBy('e.' . $selectedOrder)
        ;
        //sprawdzenie języka
//        if ( $this->getSelectedLanguage()->code !== Language::DEFAULT_LANGUAGE_CODE && isset( $this->getSelectedLanguage()->BaseUserLanguageOne ) ) {
//            $this->eventListQuery->leftJoin( 'e.EventLanguageOne elo WITH elo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId() );
//        }
        //sprawdzenie tytułu z filtra
        if (!empty($search['title'])) {
            $this->eventListQuery->addWhere('et.title LIKE ?', '%' . $search['title'] . '%');
        }
        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $this->eventListQuery->addWhere('e.is_active = ?', $search['is_active']);
        }
        //sprawdzenie czy archiwalna
        if (mb_strlen($search['is_archive']) > 0) {
            $this->eventListQuery->addWhere('e.is_archive = ?', $search['is_archive']);
        }

        if (!$this->userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $this->eventListQuery->addWhere('e.id_user_created = ?', $this->userAuth->getId());
        }

        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
        $pager = new Doctrine_Pager($this->eventListQuery, $this->_getParam('page', 1), 20);
        $eventList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->eventList = $eventList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_events-list'));
    }

    //Zmiana statusu (aktywny/nie aktywny) na podstronie listy.
    public function statusAction()
    {
        //pobranie elemntu po polu hash
        $this->event = Event::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy dany element istnieje
        $this->forward404Unless($this->event, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        //zmiana statusu
        $this->event->is_active = !(bool) $this->event->is_active;
        //zapis do bazy
        $this->event->save();
        //przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'admin_event');
    }

    public function archiveAction()
    {
        //pobranie elemntu po polu hash
        $this->event = Event::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy dany element istnieje
        $this->forward404Unless($this->event, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        $this->event->is_archive = !(bool) $this->event->is_archive;
        //zapis do bazy
        $this->event->save();
        //przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'admin_event');
    }

    public function isChatScheduleAction()
    {
        //pobranie elemntu po polu hash
        $this->event = Event::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy dany element istnieje
        $this->forward404Unless($this->event, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        //zmiana statusu
        $this->event->is_chat_schedule = !(bool) $this->event->is_chat_schedule;
        //zapis do bazy
        $this->event->save();
        //przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'admin_event');
    }

    //dodanie nowego
    public function newAction()
    {
        //tworzenie nowej instancji
        $engineUtils = Engine_Utils::getInstance();
        //tworzenie nowego elementu
        $this->event = new Event();
        $this->event->hash = $engineUtils->getHash();
        $this->event->BaseUser = $this->getSelectedBaseUser();
        $this->event->UserCreated = $this->userAuth;

//        $this->event->Translation = new EventTranslation();
//        $this->event->Translation->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;

        // ustawienie domyslnych dat poczatku i startu wydarzenia
        $this->event->setDateStart(date('Y-m-d'));
        $this->event->setDateEnd(date('Y-m-d', strtotime($this->event->getDateStart() . ' + 7 days')));

        $this->formEvent();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_event_new'));
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event-new',
            'url' => $this->view->url([], 'admin_event_new'),
        ];
    }

    //edycja
    public function editAction()
    {
        $this->event = Event::findOneByHash($this->getParam('hash'));
        $this->forward404Unless($this->event, 'Event NOT Exists (' . $this->_getParam('hash') . ')');

        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        //wyslnie danych do prywatnej funkcji generującej formularz
        $this->formEvent();

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_event_edit'));
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event-edit',
        ];
    }

    //usuwanie
    public function deleteAction()
    {
        //pobranie obiektu po polu hash
        $this->event = Event::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->event, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );
        //usuwanie całego obiektu
        $this->event->delete();

        $this->_flash->succes->addMessage($this->view->translate('cms_message_delete_success'));
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'admin_event');
    }

    private function formEvent()
    {
        $this->_helper->viewRenderer('form-event');
        $this->formEvent = $this->getFormEvent();

        if ($this->_request->isPost() && $this->formEvent->isValid($this->_request->getPost())) {
            $this->eventGetRequest();
            $this->formEvent->populateBrandForm();
            $this->event->save();
            $this->_flash->succes->addMessage($this->view->translate('cms_message_save_success'));
            $this->_redirector->gotoRouteAndExit(['hash' => $this->event->hash], 'admin_event_edit');
        }

        $this->view->formEvent = $this->formEvent;
        $this->view->event = $this->event;
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function eventGetRequest()
    {
        if (isset($_FILES['image']) && 0 === $_FILES['image']['error']) {
            $image = Service_Image::createImage(
                $this->event,
                [
                    'type' => $_FILES['image']['type'],
                    'name' => $_FILES['image']['name'],
                    'source' => $_FILES['image']['tmp_name'], ]
            );

            $image->save();
            $this->event->id_image = $image->getId();
        }

        if (isset($_FILES['image_light']) && 0 === $_FILES['image_light']['error']) {
            $image = Service_Image::createImage(
                $this->event,
                [
                    'type' => $_FILES['image_light']['type'],
                    'name' => $_FILES['image_light']['name'],
                    'source' => $_FILES['image_light']['tmp_name'], ]
            );

            $image->save();
            $this->event->id_image_light = $image->getId();
        }

        if (isset($_FILES['event_miniature']) && 0 === $_FILES['event_miniature']['error']) {
            $image = Service_Image::createImage(
                $this->event,
                [
                    'type' => $_FILES['event_miniature']['type'],
                    'name' => $_FILES['event_miniature']['name'],
                    'source' => $_FILES['event_miniature']['tmp_name'], ]
            );

            $image->save();
            $this->event->id_event_miniature = $image->getId();
        }

        if (isset($_FILES['bg_image']) && 0 === $_FILES['bg_image']['error']) {
            $image = Service_Image::createImage(
                $this->event,
                [
                    'type' => $_FILES['bg_image']['type'],
                    'name' => $_FILES['bg_image']['name'],
                    'source' => $_FILES['bg_image']['tmp_name'], ]
            );

            $image->save();
            $this->event->bg_id_image = $image->getId();
        }
    }

    /**
     * @return Event_Form_Element
     */
    private function getFormEvent()
    {
        //Pobranie formularza event/from/Element.php
        $formEvent = new Event_Form_Element(['event' => $this->event]);
        $formEvent->addSEOGroup();
        $formEvent->addBrandToForm();
        $formEvent->addOfferGroup();

        $params = ['uriFilter' => true];

        //sprawdzenie czy mamy doczynienia z nowym elementem czy edycją już istniejącego
        if (false === $this->event->isNew()) {
            $params[] = ['id_event', '!=', $this->event->getId()];
        }

        $model = 'EventTranslation';
        $params[] = ['id_language', '=', $this->event->getTranslation()->id_language];

        //budowa validatora uniemożliwiającego zapisanie o tytule który już istnieje
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken($model, 'uri', $params);
        //Dodanie walidatora do pola title w fomularzu
        $formEvent->getElement('uri')->addValidator($vAlreadyTaken);

        return $formEvent;
    }
}
