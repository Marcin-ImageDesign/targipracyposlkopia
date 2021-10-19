<?php

class Event_IndexController extends Engine_Controller_Frontend
{
    /**
     * @var mixed
     */
    public $event;

    /**
     * @var \EventFile|mixed
     */
    public $exhibFile;

    /**
     * @var EventHallMap
     */
    private $_eventHallMap;

    private $_url_webinar;

    private $_user;

    private $_autologinVar = "adasd#909kjkaasdo2'';.[s87hnsk]";

    public function preDispatch()
    {
        parent::preDispatch();
        if (!in_array($this->_request->getActionName(), ['site', 'rules'], true)) {
            $this->_breadcrumb[] = [
                'url' => $this->view->url(),
                'label' => $this->view->translate($this->addActualBreadcrumb()),
            ];
        }
    }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender() && $this->_helper->layout->isEnabled()) {
            $this->view->headMeta()->setName('og:image', '/_images/frontend/default/fb_img.png?2');
            if ('hall' !== $this->_request->getActionName()) {
                $this->renderNewsToPlaceholder('news/_section_nav.phtml', 'section-nav-content');
            }
        }
    }

    public function shareFacebookAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Statystyki
        // Sprawdzam czy punkty zostały juz naliczone. Jeśli nie to naliczam
        if (!Statistics::checkEntry(Statistics::CHANNEL_EVENT_FACEBOOK_SHARE, $this->getSelectedEvent()->getId(), $this->getSelectedEvent()->getId())) {
            Statistics_Service_Manager::add(Statistics::CHANNEL_EVENT_FACEBOOK_SHARE, $this->getSelectedEvent()->getId(), $this->getSelectedEvent()->getId());
        }
    }

    public function hallStandsPreviewAction()
    {
        $this->_helper->layout->setLayout('layout_empty');
        $this->_helper->viewRenderer('hall-map-iframe');
        $this->event = Event::findOneByHash($this->_getParam('event_hash'));
        //jeśli szablon hali, musimy pobrać mapę nie uwzględniając eventu
        if (1 === $this->_getParam('is_template')) {
            $eventHallMap = EventHallMap::findTemplateByUri($this->_getParam('hall_uri'));
        } else {
            $eventHallMap = EventHallMap::findOneByUriAndIdEvent($this->_getParam('hall_uri'), $this->event->getId());
        }

        //standardowe wymiary iframe z podglądem hali
        $iframe_width = 950;
        $iframe_height = 550;
        $x_factor = round($eventHallMap->height / $iframe_height, 2);
        $y_factor = round($eventHallMap->width / $iframe_width, 2);
        $this->view->eventHallMap = $eventHallMap;

        $standNumberList = Doctrine_Query::create()
            ->from('EventStandNumber esn')
            ->where('esn.is_active = 1')
            ->addWhere('esn.id_event_hall_map = ?', $eventHallMap->getId())
            ->addWhere('esn.logo_pos_x != 0 AND esn.logo_pos_y != 0')
            ->execute();

        $this->view->iframe_width = $iframe_width;
        $this->view->iframe_height = $iframe_height;
        $this->view->x_factor = $x_factor;
        $this->view->y_factor = $y_factor;
        $this->view->selectedAction = $this->_request->getActionName();
        $this->view->standNumberList = $standNumberList;
    }

    public function hallAction()
    {
        // get hall map from request id isset
        if ($this->hasParam('hall_uri')) {
            $this->_eventHallMap = EventHallMap::findOneByUriAndIdEvent($this->_getParam('hall_uri'), $this->getSelectedEvent()->getId());
        }

        $hall_uri = $this->getParam('hall_uri', 'main_hall');
        // get default Hall Map if request is empty
        if (!$this->_eventHallMap) {
            $this->_eventHallMap = EventHallMap::find($this->getSelectedEvent()->id_event_hall_map);
        }
        $this->forward404Unless($this->_eventHallMap);

        //ustawiamy aktywną halę
        $this->setActiveEventHall($this->_eventHallMap->getId());

        $event_halls = EventHallMap::getHallMapsByEvent($this->getSelectedEvent()->getId());
        $this->view->event_halls = $event_halls;
        $this->view->hall_uri = $hall_uri;
        $this->view->event_halls = $event_halls;
        $this->view->selectedEvent = $this->getSelectedEvent();
        $this->view->eventHallMap = $this->_eventHallMap;
        $this->renderExhibitorsToPlaceholder('event/_section_nav.phtml', 'section-nav-content', $this->_eventHallMap->getId());

        //metatagi
        $this->view->headMeta()->setName('description', $this->view->translate('meta_plan_stands_companies_brand') . ' ' . $this->getSelectedEvent()->getTitle() . ' ' . $this->view->translate('meta_in_virtual_zumi_hall'));
//        $this->view->headTitle($this->view->translate('meta_event').' '.$this->getSelectedEvent()->getTitle().' - '.$this->view->translate('meta_zumi'));

//        $this->view->groupStatusList = $groupStatusList;
//        2014.03.04 - zapytanie zostało zakomentowanie - wynik zapytania nie był nigdzie wykorzystywany
//        jeśli wszystko będzie ok można usunać
//        $exhibitorList = Doctrine_Query::create()
//            ->from('ExhibStand es')
//            ->innerJoin('es.Translations t WITH t.id_language = ? INDEXBY t.id_language', Engine_I18n::getLangId())
//            ->leftJoin('es.ExhibStandHasBrand')
//            ->where('es.is_active = 1 AND es.id_exhib_stand_type = ? AND es.id_event = ?', array(ExhibStandType::STANDARD, $this->getSelectedEvent()->getId()))
//            ->addWhere('es.id_stand_type = ?', ExhibStand::TYPE_STANDARD)
//            ->execute();
//        $this->view->exhibitorList = $exhibitorList;

        //stoiska na hali - standardowy widok
        $standNumberList = Doctrine_Query::create()
            ->select('esn.id_stand_level, esn.logo_pos_y, esn.logo_pos_x, es.id_image_logo, es.live_chat_group_id, es.css_class, t.uri, t.name, t.short_info')
            ->from('EventStandNumber esn')
            ->innerJoin('esn.ExhibStand es')
            ->innerJoin('es.Translations t WITH t.id_language = ? INDEXBY t.id_language', Engine_I18n::getLangId())
            ->where('es.is_active = 1')
            ->addWhere('esn.logo_pos_x != 0 AND esn.logo_pos_y != 0')
            ->addWhere('es.id_event = ? AND es.id_stand_type = ?', [$this->getSelectedEvent()->getId(), ExhibStand::TYPE_STANDARD])
            ->addWhere('esn.id_event_hall_map = ?', $this->_eventHallMap->getId())
            ->orderBy('FIELD(esn.id_stand_level, 1,2,4,3) ASC, esn.number DESC')
            ->fetchArray();

        //stoiska na hali - podgląd numeracji
//        $standNumberList = Doctrine_Query::create()
//            ->from('EventStandNumber esn')
//            ->addWhere('esn.id_event_hall_map = 8')
//            ->execute();

        $this->view->standNumberList = $standNumberList;
        $this->view->exhib_stands = ExhibStand::findStands($this->getSelectedBaseUser()->getId(), $this->getSelectedEvent()->getId());

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_HALL_VIEW, $this->getSelectedEvent()->getId(), $this->getSelectedEvent()->getId());
    }

    public function exhibitorsAction()
    {
        //metatagi
        $this->view->headMeta()->setName('description', $this->view->translate('meta_exhibitors_list_participating_in_event') . ' ' . $this->getSelectedEvent()->getTitle() . ' ' . $this->view->translate('meta_zumi'));
        $this->view->headTitle($this->view->translate('meta_exhibitors_list_event') . ' ' . $this->getSelectedEvent()->getTitle() . ' - ' . $this->view->translate('meta_zumi'));

        $this->view->groupStatusList = [];

        //lista wojewodztw
        $provinceList = Doctrine_Core::getTable('AddressProvince')->createQuery('ap')
            ->select('ap.*')
            ->execute()
            ->toKeyValueArray('id_address_province', 'name');
        //koniec
        //Lista branż
        $brandList = Brand::getFullBrandList($this->getSelectedEvent()->getId());
        //koniec

        $exhibStandList = Doctrine_Query::create()
            ->from('ExhibStand es')
            ->innerJoin('es.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('es.AddressProvince ap')
            ->leftJoin('es.ExhibStandHasBrand')
            ->where('es.is_active = 1 AND es.id_exhib_stand_type = ?', ExhibStandType::STANDARD)
            ->addWhere('es.id_event = ?', $this->getSelectedEvent()->getId())
            ->addWhere('es.id_stand_type = ?', ExhibStand::TYPE_STANDARD)
            ->orderBy('t.name ASC')
            ->execute();

        $this->view->brandList = $brandList;
        $this->view->provinceList = $provinceList;
        $this->view->exhibStandList = $exhibStandList;
    }

    public function receptionAction()
    {
        $sideBanners = $this->getSelectedEvent()->getBannersByGroup(EventBannerGroup::BANNER_RECEPTION_GROUP);
        shuffle($sideBanners);
        $this->view->sideBanners = $sideBanners;

        // ukrycie linku w głównym MENU do recepcji
        $this->view->hideLinkRecepion = true;

        $background_data = EventBackground::findOneByName('standard_reception');
        $this->view->background_data = $background_data;
        $this->view->selectedAction = $this->_request->getActionName();
        $this->view->show_webinars = Engine_Variable::getInstance()->getVariable(Variable::SHOW_WEBINARS, $this->getSelectedBaseUser()->getId());

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_RECEPTION_VIEW, null, $this->getSelectedEvent()->getId());
    }

    public function webinarViewAction()
    {
        $id_webinar = (int)$this->_getParam('id_webinar');
        $md5_webinar = $this->_getParam('md5_webinar');
        $this->_url_webinar = $this->_getParam('url_webinar');
        if (!$this->userAuth->getIsFullRegistration()) {
            $this->view->showExtendedRegistration = true;
            $form = new User_Form_RegisterExtended($this->userAuth, $this->_url_webinar);
            $form->getElement('position')->setDescription(null);
            $form->getElement('company_position')->setAttrib('style', 'width:372px!important;');
            $form->getElement('company_position_detail')->setAttrib('style', 'width:372px!important');
            $form->getElement('company_position_detail_2')->setAttrib('style', 'width:372px!important');
            $form->getElement('company_id_brand')->setAttrib('style', 'width:372px!important;');

            $form->getElement('company_position')->addValidator(new Zend_Validate_Between(['min' => 1, 'max' => '5']));
            $form->getElement('company_position')->setRequired(true);
            if (5 !== $this->userAuth->getCompanyPosition()) {
                $form->getElement('position')->setRequired(true);
                $form->getElement('company_id_brand')->setRequired(true);
                $form->getElement('company_city')->setRequired(true);
                if ($this->userAuth->getCompany()) {
                    $form->getElement('company_post_code')->setRequired(true);
                }
            }

            if ($this->_request->isPost()) {
                if ($form->isValid($this->_request->getPost())) {
                    $this->userAuth->save();
                    $auth = Zend_Auth::getInstance();
                    $auth->getStorage()->write($this->userAuth);
                    $this->_url_webinar = $form->url_webinar->getValue();
                    if ($this->userAuth->getIsFullRegistration()) {
                        $this->view->showExtendedRegistration = false;
                        $this->showWebinar($id_webinar, $this->_url_webinar, $md5_webinar);
                    }
                } else {
                    $this->jsonResult = ['error' => true];
                }
            }
            $this->view->id_webinar = $id_webinar;
            $this->view->url_webinar = $this->_url_webinar;
            $this->view->registerExtendedForm = $form;
        } else {
            $this->showWebinar($id_webinar, $this->_url_webinar, $md5_webinar);
        }
    }

    public function siteAction()
    {
        $eventSite = EventSite::findOneByUri($this->_getParam('site_uri'), $this->getSelectedLanguage(), $this->getSelectedEvent()->getId());


        $this->forward404Unless($eventSite, 'Stona nie istnieje (' . $this->_getParam('site_uri') . ')');

        $this->view->eventSite = $eventSite;
        $this->view->hide_title = (bool)$this->_getParam('hide_title');

        $this->_breadcrumb[] = [
            'url' => $this->view->url(),
            'label' => $eventSite->getTitle(),
        ];
    }

    public function webinarAction()
    {
        $eventSite = EventSite::findOneByUri('webinaria', $this->getSelectedLanguage(), $this->getSelectedEvent()->getId());
        $this->forward404Unless($eventSite, 'Stona nie istnieje (' . $this->_getParam('site_uri') . ')');
        $this->_user = User::findOneByHash($this->userAuth->getHash());
        $this->view->eventSite = $eventSite;
        $this->view->isFullRegistration = $this->userAuth->getIsFullRegistration();
        $this->view->isExternalId = $this->_user->getExternalId();

        $this->_breadcrumb[] = [
            'url' => $this->view->url(),
            'label' => $eventSite->getTitle(),
        ];
    }

    public function checkExternalIdAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            //nie możemy skorzystać z userAuth, bo on jest trzymany w sesji i odświeża się dopiero po przelogowaniu
            $user_hash = $this->_getParam('user_hash');
            $this->_user = User::findOneByHash($user_hash);
            $external_id = $this->_user->getExternalId();
            echo $external_id ? $external_id : -1;
            exit();
        }
    }

    public function filesAction()
    {
        $event_files = EventFile::findByEventId($this->getSelectedEvent()->getId());
        $stand_files = ExhibStandFile::findByEvent($this->getSelectedEvent()->getId(), true);
        $this->view->event_files = $event_files;
        $this->view->stand_files = $stand_files;
    }

    public function downloadEventFileAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->exhibFile = EventFile::findOneByHash($this->_getParam('hash'));
        $filename = $this->exhibFile->getFileName();
        $file_path = $this->exhibFile->getRelativeFile();

        if (!empty($file_path) && file_exists($file_path)) {
            // Statystyki
            Statistics_Service_Manager::add(Statistics::CHANNEL_RECEPTION_FILE_VIEW, $this->exhibFile->getId(), $this->getSelectedEvent()->getId());

            header('Content-Type: ' . Engine_File::getFileMime($filename));
            header('Content-Length: ' . filesize($file_path));
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($file_path);
            exit();
        }
        echo $this->view->translate('label_event_index_donwload-file_file-not-exist') . ' : ' . $filename;
    }

    public function productCatalogueAction()
    {
        $offerPassword = $this->getSelectedEvent()->offer_password;

        if ($offerPassword && ($this->_session->offerPassword != $offerPassword)) {
            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'event_offer_login');
        }

        //Deklaracja tablicy z filtrami
        $search = ['id_exhib_stand' => '', 'is_promotion' => '', 'search_field' => '', 'offer_city' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('product_list_filter');
        } else {
            $this->_session->product_list_filter= new stdClass();

            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->product_list_filter->{$key} = $this->hasParam($key)
                    ? trim($this->getParam($key, $value))
                    : (isset($this->_session->product_list_filter->{$key})
                        ? $this->_session->product_list_filter->{$key}
                        : $value);
            }
        }

        if ('Podaj miasto' === $search['offer_city']) {
            unset($search['offer_city']);
        }

        // formularz filtrowania listy produktów
        $formProductSearch = new Event_Form_StandProduct_Search(['event' => $this->getSelectedEvent()]);
        $formProductSearch->setExhibitors();
        $formProductSearch->populate($search);

        $this->view->formProductSearch = $formProductSearch;

        $eventProductList = Doctrine_Query::create()
            ->from('StandProduct sp')
            ->innerJoin('sp.Translations t')
            ->innerJoin('sp.ExhibStand es')
            ->innerJoin('es.Translations st WITH st.id_language = ?', Engine_I18n::getLangId())
            ->addWhere('es.id_event = ?', $this->getSelectedEvent()->getId())
            ->addWhere('es.id_stand_type = ?', ExhibStand::TYPE_STANDARD)
            ->addWhere('t.id_language = ?', Engine_I18n::getLangId())
            ->addWhere('es.is_active = 1')
            ->orderBy('sp.is_promotion DESC, t.name ASC');

        //sprawdzenie promocji z filtra
        if (1 === $search['is_promotion']) {
            $eventProductList->addWhere('sp.is_promotion = ?', $search['is_promotion']);
        }

        //sprawdzenie wystawcy z filtra
        if (mb_strlen($search['id_exhib_stand']) > 0) {
            $eventProductList->addWhere('sp.id_exhib_stand = ?', $search['id_exhib_stand']);
        }

        //sprawdzenie pola otwartego z filtra
        if (mb_strlen($search['search_field']) > 0) {
            $eventProductList->addWhere(
                't.name LIKE ? or t.lead LIKE ? or t.keywords LIKE ?',
                [
                    '%' . $search['search_field'] . '%',
                    '%' . $search['search_field'] . '%',
                    '%' . $search['search_field'] . '%',
                ]
            );
        }

        if (isset($search['offer_city']) && !empty($search['offer_city'])) {
            $eventProductList->addWhere('sp.offer_city  LIKE ?', [$search['offer_city'] . '%']);
        }

        //paginacja
        $pager = new Doctrine_Pager($eventProductList, $this->_getParam('page', 1), 10);
        $eventProductList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->show_prices = Engine_Variable::getInstance()->getVariable(Variable::SHOW_PRICES, $this->getSelectedBaseUser()->getId());

        // to idzie gdy jest post z formularza
        if ($this->getRequest()->isPost()) {
            $url = $this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()]);

            // wczytanie zawartości tabeli (dane)
            $view = $this->view->partial(
                'event/_offerSearchResult.phtml',
                [
                    'eventProductList' => $eventProductList,
                    'selectedEvent' => $this->getSelectedEvent(),
                    'id_briefcase_type' => BriefcaseType::TYPE_BRIEFCASE_PRODUCT,
                ]
            );

            $pagination = $this->view->partial(
                'commponents/pagination.phtml',
                [
                    'pager' => $pager,
                    'pages' => $pages,
                    'pagerRange' => $pagerRange,
                ]
            );

            if ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['content_id'] = 'ajax_list';
                $this->jsonResult['message'] = $view;
                $this->jsonResult['pagination_id'] = 'pagination';
                $this->jsonResult['pagination'] = $pagination;
            } else {
                $this->_redirector->gotoUrlAndExit($url);
            }
        } else {
            // a tu leci zwykły widok

            //aktówka
            $this->view->id_briefcase_type = BriefcaseType::TYPE_BRIEFCASE_PRODUCT;
            $this->view->eventProductList = $eventProductList;
            $this->view->pager = $pager;
            $this->view->pages = $pages;
            $this->view->pagerRange = $pagerRange;
        }
    }

    public function offerLoginAction()
    {
        if ($this->getRequest()->isPost()) {
            $offerPassword = $this->getSelectedEvent()->offer_password;

            if ($this->getRequest()->getParam('password') == $offerPassword) {
                $this->_session->offerPassword = $offerPassword;

                if ($this->_session->offerPasswordRedirect) {
                    $offerPasswordRedirect = $this->_session->offerPasswordRedirect;
                    $this->_session->__unset('offerPasswordRedirect');
                    $this->_redirector->gotoUrlAndExit($offerPasswordRedirect);
                }

                $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'event_offer_catalogue');
            }
        }
    }

    public function rulesAction()
    {
    }

    public function hallChangeAction()
    {
        if ($this->hasParam('hall_uri')) {
            $this->_eventHallMap = EventHallMap::findOneByUriAndIdEvent($this->_getParam('hall_uri'), $this->getSelectedEvent()->getId());
        }

        // get default Hall Map if request is empty
        if (!$this->_eventHallMap) {
            $this->_eventHallMap = EventHallMap::find($this->getSelectedEvent()->id_event_hall_map);
            //jeśli wyciągamy domyślną, przekazujemy tę informację do widoku
            //potrzebne w miejscach gdzie nie przekazana hala - recepcja, lista wystawcow itp
            $this->view->forcedDefault = true;
        }
        $this->forward404Unless($this->_eventHallMap);

        //ustawiamy aktywną halę
        $this->setActiveEventHall($this->_eventHallMap->getId());
        $this->_helper->layout->disableLayout();
    }

    public function gamificationAction()
    {
        $eventSite = EventSite::findOneByUri('gamification_rules', $this->getSelectedLanguage(), $this->getSelectedEvent()->getId());
        $this->forward404Unless($eventSite, 'Stona nie istnieje (' . $this->_getParam('site_uri') . ')');

        $this->view->eventSite = $eventSite;
    }

    public function dayRankingAction()
    {
        // Ranking dzienny
        $dayRankingQuery = Doctrine_Query::create()
            ->from('GamificationDayRanking gup')
            ->leftJoin('gup.User u')
            ->addWhere('gup.id_event = ?', $this->getSelectedEvent()->getId())
            ->addWhere('u.is_banned = 0')
            ->orderBy('points DESC, id_user ASC');

        //paginacja
        $pager = new Doctrine_Pager($dayRankingQuery, $this->_getParam('page', 1), 10);
        $dayRankingList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->dayRankingList = $dayRankingList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        //$this->view->dayRankingList =  $dayRankingQuery->execute();
    }

    public function rankingAction()
    {
        // wczytanie danych o uczestnikach
        // lista wszystkich uczestników przypisanych na dany event
        $participantsListQuery = Doctrine_Query::create()
            ->from('GamificationUserPoints gup')
            ->leftJoin('gup.User u')
            ->leftJoin('gup.GamificationUserHistoryPoints guhp WITH guhp.id_event = ?', $this->getSelectedEvent()->getId())
            ->addWhere('u.is_banned = 0')
            ->orderBy('points DESC, id_user ASC');
        $this->view->participantsList = $participantsListQuery->execute();
    }

    private function showWebinar($id_webinar, $url_webinar, $md5_webinar)
    {
        $this->view->layout()->disableLayout();

        $id_webinar = (int)$id_webinar;
        if (md5('voxhon' . ($id_webinar + 42)) !== $md5_webinar) {
            return;
        }
        //nie możemy skorzystać z userAuth, bo on jest trzymany w sesji i odświeża się dopiero po przelogowaniu
        $this->_user = User::findOneByHash($this->userAuth->getHash());
        //zanim naliczymy punkty i przekierujemy sprawdzamy external ID z bazy idg
        if ($this->_user->getExternalId()) {
            $this->_helper->viewRenderer->setNoRender(true);

            // Statystyki
            Statistics_Service_Manager::add(Statistics::CHANNEL_RECEPTION_WEBINAR, $id_webinar, $this->getSelectedEvent()->getId());
            // przekierowanie na stronę webinariów
            // przerabiamy url na potrzeby IDG
            $external_id = $this->_user->getExternalId();
            $url_hash = md5($this->_autologinVar . $external_id . $this->_autologinVar);
            $parsed_url = 'www.idg.pl/tv/logowanie?action=login&id=' . $external_id . '&hash=' . $url_hash . '&next=' . $url_webinar;
            if ($this->_request->isXmlHttpRequest()) {
                $this->jsonResult = ['target' => 'http://' . $parsed_url];
            } else {
                $this->_helper->redirector->gotoUrl('http://' . $parsed_url);
            }
        } else {
            $this->jsonResult = ['contentTarget' => $this->view->url(['event_uri' => $this->getSelectedEvent()->getUri(),
                                                                      'id_webinar' => $id_webinar,
                                                                      'md5_webinar' => $md5_webinar,], 'event_webinar_view') . '?url_webinar=' . $url_webinar];
            $this->view->id_webinar = $id_webinar;
            $this->view->md5_webinar = $md5_webinar;
            $this->view->url_webinar = $url_webinar;
            $this->_helper->viewRenderer('webinar-wait');
        }
    }
}
