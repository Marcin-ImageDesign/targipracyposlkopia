<?php

class Event_AdminSiteController extends Engine_Controller_Admin
{
    public $formEventSite;
    /**
     * @var Event
     */
    private $event;

    /**
     * @var EventSite
     */
    private $eventSite;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-site/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->event, 'Event NOT Exists for hash: (' . $this->_getParam('hash') . ') ');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        $eventSiteQuery = Doctrine_Query::create()
            ->from('EventSite es')
            ->where('es.id_event = ?', $this->event->getId())
            ->orderBy('es.title ASC')
        ;

        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
            $eventSiteQuery->leftJoin('es.EventSiteLanguageOne eslo WITH eslo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
        }
        $eventSiteList = $eventSiteQuery->execute();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Sites list'));
        $this->view->event = $this->event;
        $this->view->eventSiteList = $eventSiteList;

        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_participation-event',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'admin_event_edit'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_site',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'event_admin-site'),
        ];
    }

    //dodanie nowego
    public function newAction()
    {
        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->event, 'Event NOT Exists for hash: (' . $this->_getParam('hash') . ') ');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        $this->eventSite = new EventSite();
        $this->eventSite->Event = $this->event;
        $this->eventSite->BaseUser = $this->getSelectedBaseUser();
        $this->eventSite->hash = $this->engineUtils->getHash();
        $this->eventSite->order = $this->eventSite->getLastOrderEventSite($this->event->getId()) + 1;

        $this->formEventSite();
        $this->view->event = $this->event;
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New site'));

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_participation-event',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'admin_event_edit'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_site',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'event_admin-site'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_site-new',
            'url' => $this->view->url(),
        ];
    }

    //edycja
    public function editAction()
    {
        //pobranie elementu po polu hash
        $this->eventSite = EventSite::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->eventSite, 'EventSite NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->eventSite->Event),
            [$this->getSelectedBaseUser()->getId(), $this->eventSite->Event->getId()]
        );

        //wyslnie danych do prywatnej funkcji generującej formularz
        $this->formEventSite();
        $this->view->event = $this->eventSite->Event;
        $this->event = $this->eventSite->Event;
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set('Edit site');

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_participation-event',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'admin_event_edit'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_site',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'event_admin-site'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_site-edit',
            'url' => $this->view->url(),
        ];
    }

    //usuwanie
    public function deleteAction()
    {
        //pobranie elementu po polu hash
        $this->eventSite = EventSite::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->eventSite, 'EventSite NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->eventSite->Event),
            [$this->getSelectedBaseUser()->getId(), $this->eventSite->Event->getId()]
        );
        //usuwanie całego obiektu
        $this->event = $this->eventSite->Event;
        $this->eventSite->delete();

        $this->_flash->success->addMessage('Item has been deleted');
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit(['hash' => $this->event->getHash()], 'event_admin-site');
    }

    public function statusAction()
    {
        $this->eventSite = EventSite::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->eventSite, 'EventPlan Not Exists hash: (' . $this->_getParam('hash') . ')');
        $this->forward404Unless(
            $this->getSelectedBaseUser()->hasAccess($this->eventSite),
            [$this->getSelectedBaseUser()->getId(), $this->eventSite->getId()]
        );

        $this->eventSite->setIsActive(!$this->eventSite->isActive());
        $this->eventSite->save();

        $this->_redirector->gotoRouteAndExit(['hash' => $this->eventSite->Event->getHash()], 'event_admin-site');
    }

    private function formEventSite()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('admin-site/form-event-site', null, true);
        //pobranie formularza dla pojedyńczego elementu
        $this->formEventSite = $this->getFormEventSite();
        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->formEventSite->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->eventSiteGetRequest();
            //zapis zmian do bazy
            $this->eventSite->save();
            //Tworzenie informacji zwrotnej
            $this->_flash->success->addMessage('Save successfully completed');
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->eventSite->Event->getHash()], 'event_admin-site');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->formEventSite = $this->formEventSite;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function eventSiteGetRequest()
    {
        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && !isset($this->eventSite->EventSiteLanguageOne)) {
            $this->eventSite->EventSiteLanguageOne = new EventSiteLanguage();
            $this->eventSite->EventSiteLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }

        if (!$this->eventSite->isProtected()) {
            $this->eventSite->setTitle($this->formEventSite->getValue('title'));
        } else {
            $this->eventSite->setTitle($this->view->translate($this->eventSite->title, $this->getSelectedLanguage()->code));
        }
        $this->eventSite->setContent($this->formEventSite->getValue('text'));
        $this->eventSite->is_active = (bool) $this->formEventSite->getValue('is_active');
        $this->eventSite->setUri($this->engineUtils->getFriendlyUri($this->eventSite->getTitle()));
    }

    private function getFormEventSite()
    {
        $formEventSite = new Event_Form_Site_Element($this->eventSite); //, $this->getSelectedLanguage()
        $params = ['uriFilter' => true];

        //sprawdzenie czy mamy doczynienia z nowym elementem czy edycją już istniejącego
        if (!$this->eventSite->isNew()) {
            $params[] = ['id_event_site', '!=', $this->eventSite->getId()];
        }

        //ustawienie zmiennych odpowiedzilanych za język
        if (isset($this->eventSite->EventSiteLanguageOne)) {
            $model = 'EventSiteLanguage';
            $params[] = ['id_base_user_language', '=', $this->eventSite->EventSiteLanguageOne->id_base_user_language];
        } else {
            $model = 'EventSite';
            $params[] = ['id_base_user', '=', $this->getSelectedBaseUser()->getId()];
        }
        //budowa validatora uniemożliwiającego zapisanie o tytule który już istnieje
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken($model, 'uri', $params);
        //Dodanie walidatora do pola title w fomularzu
        $formEventSite->getElement('title')->addValidator($vAlreadyTaken);

        return  $formEventSite;
    }
}
