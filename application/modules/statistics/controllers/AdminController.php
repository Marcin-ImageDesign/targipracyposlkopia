<?php

class Statistics_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \Statistics_Form_Filter|mixed
     */
    public $filter;
    /**
     * @var \User|mixed
     */
    public $user;
    /**
     * @var \ExhibStand|mixed
     */
    public $exhibStand;
    /**
     * Event.
     */
    private $event;

    /**
     * ExhibStand.
     */
    private $exhib_stand;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_statistics'),
            'url' => $this->view->url([], 'statistics_admin-index'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        $this->view->show_gamification = Engine_Variable::getInstance()->getVariable(Variable::SHOW_GAMIFICATION);
        if (!$this->userAuth->isAdmin()) {
            $this->view->show_gamification = false;
        }

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('_contentNav.phtml', 'nav-left');
        }
    }

//    public function postDispatch() {
//        parent::postDispatch();
//
//        if ($this->_helper->layout->isEnabled()) {
//            /* generowanie lewego menu dla modułu */
//            $this->view->renderToPlaceholder('admin-statistics/_contentNav.phtml', 'nav-left');
//        }
//    }

    public function indexAction()
    {
        if ($this->hasSelectedEvent()) {
            $this->_redirector->goToUrlAndExit($this->view->url([], 'statistics_admin-event') . '?setSelectedEvent=' . $this->getSelectedEvent()->getHash());
        } else {
            $this->_redirector->gotoRouteAndExit([], 'statistics_admin-events');
        }
    }

    public function eventsAction()
    {
        if ($this->hasSelectedEvent()) {
            $this->_redirector->gotoRouteAndExit([], 'statistics_admin-index');
        }

        //Deklaracja tablicy z filtrami
        $search = ['date_from' => '', 'date_to' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('statistic_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->statistic_filter->{$key} = $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->statistic_filter->{$key}) ? $this->_session->statistic_filter->{$key} : $value);
            }
        }

        $this->filter = new Statistics_Form_Filter();
        $this->filter->populate($search);

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Statistics'));

        $eventListQuery = Doctrine_Query::create()
            ->from('Event e INDEXBY e.id_event')
            ->leftJoin('e.ExhibParticipation p')
            ->where('e.id_base_user = ?', $this->getSelectedBaseUser()->getId())
        ;

        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
            $eventListQuery->leftJoin('e.EventLanguageOne elo WITH elo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
        }

        //filtrowanie danych dla zalogowanych
        if ($this->userAuth->isOrganizer()) {
            $eventListQuery->addWhere('p.is_active = 1 AND p.id_exhib_participation_type = ? AND p.id_user = ?', [ExhibParticipationType::TYPE_ORGANIZER, $this->userAuth->getId()]);
        } elseif ($this->userAuth->isExhibotor()) {
            $eventListQuery->leftJoin('p.ExhibStandParticipation esp');
            $eventListQuery->addWhere('p.is_active = 1 AND esp.is_active = 1 AND p.id_exhib_participation_type = ? AND p.id_user = ? ', [ExhibParticipationType::TYPE_EXHIBITOR, $this->userAuth->getId()]);
        }

        $eventList = $eventListQuery->execute();
        $this->view->eventList = $eventList;
        $this->view->filter = $this->filter;

        $eventArray = [];

        foreach ($eventList as $event) {
            $eventArray[] = $event->getId();
        }

        $exhib_stands = ExhibStand::findAll();

        $exhibitorStandsArray = [];
        foreach ($exhib_stands as $stand) {
            $exhibitorStandsArray[] = $stand->getId();
        }

        //wyciągnięcie statystyk eventu
        $channel = [Statistics::CHANNEL_STAND_VIEW, Statistics::CHANNEL_HALL_VIEW];
        $query = Doctrine_Query::create()
            ->select('s.id_event, s.channel, count(*)')
            ->from('Statistics s')
            ->where('s.id_base_user = ? ', $this->getSelectedBaseUser()->getId())
            ->whereIn('s.channel', $channel)
            ->whereIn('s.id_event', $eventArray)
            ->whereIn('s.id_element', $exhibitorStandsArray)
        ;

        if (!empty($search['date_from'])) {
            $query->addWhere('s.created_at > ?', $search['date_from'] . ' 00:00:00');
        }

        if (!empty($search['date_to'])) {
            $query->addWhere('s.created_at < ?', $search['date_to'] . ' 23:59:59');
        }

        $query->groupBy('s.id_event, s.channel ');

        //filtrowanie danych dla zalogowanych
        if ($this->userAuth->isOrganizer()) {
            $query->leftJoin('s.Event e')
                ->leftJoin('e.ExhibParticipation p')
                ->addWhere('p.is_active = 1 AND p.id_user = ?', $this->userAuth->getId())
                ->addWhere('p.id_event IN ?', [array_keys($eventList->toArray())])
                ->addWhere('p.id_exhib_participation_type = ?', ExhibParticipationType::TYPE_ORGANIZER)
            ;
        }

        $statistics = $query->fetchArray();
        $statisticsList = [];
        foreach ($statistics as $arr) {
            $statisticsList[$arr['id_event']][$arr['channel']] = $arr['count'];
        }

        $this->view->statisticsList = $statisticsList;
        $this->view->search = $search;
    }

    /**
     * Grywalizacja
     * metoda działa w czasie rzeczywistym i obsługuje filtry.
     */
    public function gamification2Action()
    {
        if (!$this->hasSelectedEvent()) {
            $this->_redirector->gotoRouteAndExit([], 'statistics_admin-index');
        }

        //Deklaracja tablicy z filtrami
        $search = ['date_from' => '', 'date_to' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('gamification2_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->gamification2_filter->{$key} = $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->gamification2_filter->{$key}) ? $this->_session->gamification2_filter->{$key} : $value);
            }
        }
        $this->filter = new Statistics_Form_Filter();
        $this->filter->populate($search);
        $this->view->filter = $this->filter;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Event - gamification'));
        $this->view->event = $this->getSelectedEvent();

        /*
        // lista wszystkich uczestników przypisanych na dany event
        $participantsListQuery2 = Doctrine_Query::create()
            ->from('GamificationUserPoints gup')
            ->leftJoin('gup.User u')
            ->where('gup.id_event = ? ', $this->getSelectedEvent()->getId() )
            ->orderBy('points DESC, id_user ASC');
        */

        $date_from = '';
        if (!empty($search['date_from'])) {
            $date_from = ' AND s.created_at > "' . $search['date_from'] . ' 00:00:00' . '"';
        }

        $date_to = '';
        if (!empty($search['date_to'])) {
            $date_to = ' AND s.created_at < "' . $search['date_to'] . ' 23:59:59' . '"';
        }

        // lista wszystkich uczestników przypisanych na dany event
        $participantsListQuery3 = '
        SELECT up.id_user, up.id_event, SUM( up.action_points ) AS points, u.hash, u.first_name, u.last_name, u.email, u.is_banned
        FROM (
            SELECT  s.id_user AS id_user,
            s.id_event AS id_event,
            gap.action AS ACTION,
            COUNT(*),
            gap.point AS action_points
        FROM statistics s, gamification_action_point gap
            WHERE s.channel = gap.action
            AND s.id_event = ' . $this->getSelectedEvent()->getId() .
            $date_from .
            $date_to . '
            GROUP BY DAY(s.created_at), gap.action, s.id_user, s.id_element, s.id_element_item
            ORDER BY s.id_user
        ) AS up
        LEFT JOIN exhib_participation ep ON ep.id_user = up.id_user
        LEFT JOIN user u ON u.id_user = up.id_user
        WHERE ep.id_exhib_participation_type = 5 AND ep.id_event = ' . $this->getSelectedEvent()->getId() . '
        GROUP BY up.id_user
        ORDER BY points DESC;
        ';
        $conn = Doctrine_Manager::connection();
        $participantsList = $conn->prepare($participantsListQuery3);
        $participantsList->execute();
        $this->view->participantsList = $participantsList->fetchAll();

        //$participantsListQuery = Doctrine_Query::create()->parseSelect($participantsListQuery3);
        //$participantsListQuery = Doctrine_Query::create()->parseDqlQuery($participantsListQuery3);

        /*
        $pager = new Doctrine_Pager($participantsListQuery, $this->_getParam('page', 1), 20);
        $participantsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => 5), $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->participantsList = $participantsList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        */
    }

    /**
     * Grywalizacja
     * metoda czyta z zapisanych danych - nie działa w czasie rzeczywistym i nie obsługuje filtrów.
     */
    public function gamificationAction()
    {
        if (!$this->hasSelectedEvent()) {
            $this->_redirector->gotoRouteAndExit([], 'statistics_admin-index');
        }

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Event - gamification'));
        $this->view->event = $this->getSelectedEvent();

        // lista wszystkich uczestników przypisanych na dany event
        $participantsListQuery = Doctrine_Query::create()
            ->from('GamificationUserPoints gup')
            ->leftJoin('gup.User u')
            ->where('gup.id_event = ? ', $this->getSelectedEvent()->getId())
            ->orderBy('points DESC, id_user ASC')
        ;

        $pager = new Doctrine_Pager($participantsListQuery, $this->_getParam('page', 1), 20);
        $participantsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->participantsList = $participantsList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
    }

    /**
     * Grywalizacja - przeliczanie punktów.
     */
    public function gamificationConvertPointsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // historia listy porządkowej uczestników
        GamificationUserHistoryPoints::usersHistoryPoints($this->getSelectedEvent()->getId());

        //obliczanie punktów
        GamificationUserPoints::convertUsersPoints($this->getSelectedEvent()->getId());

        //ranking dzienny
        GamificationUserPoints::calculateDayRanking($this->getSelectedEvent()->getId());

        $this->_redirector->gotoRouteAndExit([], 'statistics_admin-gamification2');
    }

    /**
     * Grywalizacja - szczegóły zliczania punktw dla pojedynczego użytkownika.
     */
    public function gamificationUserPointsAction()
    {
        $this->user = User::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->user, 'User Not Found hash: (' . $this->_getParam('hash') . ')');
        $this->view->user = $this->user;

        //Deklaracja tablicy z filtrami
        $search = ['date_from' => '', 'date_to' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('gamification_user_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->gamification_user_filter->{$key} = $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->gamification_user_filter->{$key}) ? $this->_session->gamification_user_filter->{$key} : $value);
            }
        }
        $this->filter = new Statistics_Form_Filter();
        $this->filter->populate($search);
        $this->view->filter = $this->filter;

        $date_from = null;
        if (!empty($search['date_from'])) {
            $date_from = $search['date_from'] . ' 00:00:00';
        }

        $date_to = null;
        if (!empty($search['date_to'])) {
            $date_to = $search['date_to'] . ' 23:59:59';
        }

        $user_points = GamificationUserPoints::getUserPoints($this->getSelectedEvent()->getId(), $this->user->getId(), $date_from, $date_to);

        $this->view->pointsList = $user_points;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Event - gamification - user'));
        $this->view->event = $this->getSelectedEvent();
    }

    public function eventAction()
    {
        if (!$this->hasSelectedEvent()) {
            $this->_redirector->gotoRouteAndExit([], 'statistics_admin-index');
        }

        //Deklaracja tablicy z filtrami
        $search = ['date_from' => '', 'date_to' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('statistic_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->statistic_filter->{$key} = $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->statistic_filter->{$key}) ? $this->_session->statistic_filter->{$key} : $value);
            }
        }

        $this->filter = new Statistics_Form_Filter();
        $this->filter->populate($search);

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Event - statistics'));
        $exhib_stands = ExhibStand::findStandsByAuthUser($this->getSelectedBaseUser(), $this->userAuth, $this->getSelectedEvent()->getId());

        $this->view->exhib_stands = $exhib_stands;

        $exhibitorStandsArray = [];
        foreach ($exhib_stands as $stand) {
            $exhibitorStandsArray[] = $stand->getId();
        }

        //wyciągnięcie statystyk stoisk
        $query = Doctrine_Query::create()
            ->select('s.id_element, s.channel, count(*)')
            ->from('Statistics s')
                // ->where(' s.channel = ? ', Statistics::CHANNEL_STAND_VIEW)
            ->where('s.id_event = ?', [$this->getSelectedEvent()->getId()])
            ->addWhere('s.id_element IN ?', [$exhibitorStandsArray])
            ->groupBy('s.id_element, s.channel')
        ;

        if (!empty($search['date_from'])) {
            $query->addWhere('s.created_at > ?', $search['date_from'] . ' 00:00:00');
        }

        if (!empty($search['date_to'])) {
            $query->addWhere('s.created_at < ?', $search['date_to'] . ' 23:59:59');
        }

        $statistics = $query->fetchArray();

        $statisticsList = [];
        $global = ['stand_view' => 0, 'stand_contact_view' => 0, 'stand_product_viewlist' => 0, 'stand_product_view' => 0, 'stand_file_view' => 0, 'stand_facebook_view' => 0, 'stand_shop_view' => 0];
        foreach ($statistics as $arr) {
            $statisticsList[$arr['id_element']][$arr['channel']] = $arr['count'];
        }

        foreach ($statisticsList as $stat) {
            $global['stand_view'] += isset($stat['stand_view']) ? (int) $stat['stand_view'] : 0;
            $global['stand_product_viewlist'] += isset($stat['stand_product_viewlist']) ? (int) $stat['stand_product_viewlist'] : 0;
            $global['stand_product_view'] += isset($stat['stand_product_view']) ? (int) $stat['stand_product_view'] : 0;
            $global['stand_contact_view'] += isset($stat['stand_contact_view']) ? (int) $stat['stand_contact_view'] : 0;
            $global['stand_facebook_view'] += isset($stat['stand_facebook_view']) ? (int) $stat['stand_facebook_view'] : 0;
            $global['stand_file_view'] += isset($stat['stand_file_view']) ? (int) $stat['stand_file_view'] : 0;
            $global['stand_shop_view'] += isset($stat['stand_shop_view']) ? (int) $stat['stand_shop_view'] : 0;
        }

        $this->view->global = $global;
        $this->view->statisticsList = $statisticsList;
        $this->view->event = $this->getSelectedEvent();
        $this->view->filter = $this->filter;
        $this->view->search = $search;

        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_participation_statistics_event'),
            'url' => $this->view->url(['hash' => $this->getSelectedEvent()->getHash()], 'statistics_admin-event'),
        ];
        $this->view->showExportLinkEvent = true;

        //od sesji nie przekazujemy doctrine collection tylko tablicę potrzebnych elementów
        $stands_minified = [];
        //dla multi hal dociągamy nazwę hali
        $has_multi_halls = count(EventHallMap::getHallMapsByEvent($this->getSelectedEvent()->getId())) > 0;
        foreach ($exhib_stands as $key => $stand) {
            $stands_minified[$key]['name'] = $stand->getName();
            $stands_minified[$key]['id'] = $stand->getId();
            if ($has_multi_halls) {
                $stands_minified[$key]['hall_name'] = $stand->EventStandNumber->EventHallMap->getName();
            }
        }

        // zapisanie wyniku do sesji - potrzebne do exportu do CSV
        $statistics = new Zend_Session_Namespace('statistics');
        $statistics->exhib_stands = $stands_minified;
        $statistics->statisticsListEvent = $statisticsList;
    }

    // public function eventDetailsAction() {
    //     $this->event = Event::findOneByHash($this->_getParam('hash'));
    //     $this->forward404Unless($this->event, 'Event NOT Exists (' . $this->_getParam('hash') . ')');
    //     //sprawdzenie czy dla baseuser są prawa dostępu
    //     $this->forward403Unless(
    //     $this->getSelectedBaseUser()->hasAccess($this->event), array($this->getSelectedBaseUser()->getId(), $this->event->getId())
    //     );

    //     $this->view->placeholder('headling_1_content')->set($this->view->translate('Event details - statistics'));
    //     $this->view->exhib_event_tv_movies = ExhibEventTvMovie::getTvByEventId($this->event->getId());
    //     $this->view->statisticsList = ExhibStatistics::getStatisticsTvEvent($this->event->getId());
    //     $this->view->event = $this->event;
    // }

    public function standViewAction()
    {
        //Deklaracja tablicy z filtrami
        $search = ['date_from' => '', 'date_to' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('statistic_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->statistic_filter->{$key} = $this->hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->statistic_filter->{$key}) ? $this->_session->statistic_filter->{$key} : $value);
            }
        }

        $this->filter = new Statistics_Form_Filter();
        $this->filter->populate($search);

        $this->exhib_stand = ExhibStand::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser());
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Stand view - statistics'));
        $this->checkExhibStandAccess();
        $this->view->exhib_stand = $this->exhib_stand;
        $this->view->event = $this->getSelectedEvent();
        $statisticsQuery = Doctrine_Query::create()
            ->select(' s.id_user, u.first_name, u.last_name, s.channel, count(*)')
            ->from('Statistics s')
            ->leftJoin('s.User u')
            ->where(
                ' s.channel = ? AND s.id_element = ? AND s.id_base_user = ?',
                [Statistics::CHANNEL_STAND_VIEW, $this->exhib_stand->getId(), $this->getSelectedBaseUser()->getId()]
            )
            ->groupBy(' s.id_user')
        ;

        if (!empty($search['date_from'])) {
            $statisticsQuery->addWhere('s.created_at > ?', $search['date_from'] . ' 00:00:00');
        }

        if (!empty($search['date_to'])) {
            $statisticsQuery->addWhere('s.created_at < ?', $search['date_to'] . ' 23:59:59');
        }
        $statisticsList = $statisticsQuery->execute();
        $this->view->statisticsList = $statisticsList;
        $this->view->filter = $this->filter;
        $this->view->search = $search;
        $this->view->showExportLinkStand = true;

        // zapisanie wyniku do sesji - potrzebne do exportu do CSV
        $statistics = new Zend_Session_Namespace('statistics');
        $statistics->statisticsListStand = $statisticsList;

        // $this->view->statisticsList = ExhibStatistics::getStatisticsStandView($this->exhib_stand->getId(), $this->getSelectedBaseUser()->getId());

        // $this->_breadcrumb[] = array(
        //     'label' => $this->view->translate('breadcrumb_cms_participation_statistics_event'),
        //     'url' => $this->view->url(array('hash'=>$this->exhib_stand->ExhibStand>getFirst()->Exhib->Event->getHash()), 'statistics_admin-event')
        // );

        // $this->_breadcrumb[] = array(
        //     'label' => $this->view->translate('breadcrumb_cms_participation_statistics_stand-view'),
        //     'url' => $this->view->url(array('hash'=>$this->exhib_stand->getHash()), 'statistics_admin-stand-view')
        // );
    }

    // public function downloadStandFilesAction() {
    //     $this->exhib_stand = ExhibStand::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser());
    //     $this->view->placeholder('headling_1_content')->set($this->view->translate('Stand - download file statistics'));
    //     $this->checkExhibStandAccess();
    //     $this->view->exhib_stand = $this->exhib_stand;

    //     $this->view->statisticsList = ExhibStatistics::getStatisticsDownloadStandFiles($this->exhib_stand->getId(), $this->getSelectedBaseUser()->getId());

    //     $this->_breadcrumb[] = array(
    //         'label' => $this->view->translate('breadcrumb_cms_participation_statistics_event'),
    //         'url' => $this->view->url(array('hash'=>$this->exhib_stand->ExhibStand>getFirst()->Exhib->Event->getHash()), 'statistics_admin-event')
    //     );

    //     $this->_breadcrumb[] = array(
    //         'label' => $this->view->translate('breadcrumb_cms_participation_statistics_download-stand-files'),
    //         'url' => $this->view->url(array('hash'=>$this->exhib_stand->getHash()), 'statistics_admin-stand-view')
    //     );
    // }

    public function statisticsHistoryAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $exhib_statistics_type = $this->_getParam('type');
        $date_from = $this->_getParam('date_from');
        $date_to = $this->_getParam('date_to');

        $columns = [['name' => $this->view->translate('Date'), 'type' => 'base']];
        switch ($exhib_statistics_type) {
                case 'events_view':
                    $column_name = $this->view->translate('Stands views');
                    $columns[] = ['name' => $column_name, 'type' => Statistics::CHANNEL_STAND_VIEW];
                        echo Zend_Json::encode(
                            [
                                'data' => Statistics::getStatisticsHistory(Statistics::CHANNEL_STAND_VIEW, $date_from, $date_to, null, null),
                                'columns' => $columns, ]
                        );

                    break;
                case 'event_stands_view':
                $this->event = Event::findOneByHash($this->_getParam('hash'));
                $exhib_stands = ExhibStand::findStandsByAuthUser($this->getSelectedBaseUser(), $this->userAuth, $this->getSelectedEvent()->getId());
                $exhibitorStandsArray = [];
                foreach ($exhib_stands as $stand) {
                    $exhibitorStandsArray[] = $stand->getId();
                }

                    $column_name = $this->view->translate('Stands views');
                    $columns[] = ['name' => $column_name, 'type' => Statistics::CHANNEL_STAND_VIEW];
                        echo Zend_Json::encode(
                            [
                                'data' => Statistics::getStatisticsHistory(Statistics::CHANNEL_STAND_VIEW, $date_from, $date_to, $this->event->getId(), $exhibitorStandsArray),
                                'columns' => $columns, ]
                        );

                    break;
                case 'stand_view':
                    $this->exhibStand = ExhibStand::findOneByHashAndBaseUser($this->_getParam('hash'), $this->getSelectedBaseUser());
                    $column_name = $this->view->translate('Stands views');
                    $columns[] = ['name' => $column_name, 'type' => Statistics::CHANNEL_STAND_VIEW];
                        echo Zend_Json::encode(
                            [
                                'data' => Statistics::getStatisticsHistory(Statistics::CHANNEL_STAND_VIEW, $date_from, $date_to, $this->exhibStand->id_event, $this->exhibStand->getId()),
                                'columns' => $columns, ]
                        );

                    break;
            }
    }

    public function exportEventAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fileName = 'event-statististics-' . time();

        $statistics = new Zend_Session_Namespace('statistics');
        $exhib_stands = $statistics->exhib_stands;
        $statisticsListEvent = $statistics->statisticsListEvent;

        header('Content-type: application/csv');
        header("Content-Disposition: attachment;charset=utf-8; filename=\"{$fileName}.csv\"");
        $fp = fopen('php://output', 'w');

        $head = ['Stand name', 'Views', 'View list of products', 'View products', 'Entry in the contact form', 'Facebook click', 'Count of downloaded files', 'Enter shop'];
        fputcsv($fp, $head, ';');

        foreach ($exhib_stands as $key => $exhib_stand) {
            $stand_name = isset($exhib_stand['hall_name']) ? $exhib_stand['name'] . ' (' . $exhib_stand['hall_name'] . ')' : $exhib_stand['name'];
            $fields = [
                $stand_name,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_VIEW])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_VIEW] : 0,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_PRODUCT_VIEWLIST])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_PRODUCT_VIEWLIST] : 0,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_PRODUCT_VIEW])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_PRODUCT_VIEW] : 0,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_CONTACT_VIEW])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_CONTACT_VIEW] : 0,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_FACEBOOK_VIEW])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_FACEBOOK_VIEW] : 0,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_FILE_VIEW])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_FILE_VIEW] : 0,
                isset($statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_SHOP_VIEW])
                ? $statisticsListEvent[$exhib_stand['id']][Statistics::CHANNEL_STAND_SHOP_VIEW] : 0,
            ];
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);
    }

    public function exportStandAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fileName = 'stand-statististics-' . time();

        $statistics = new Zend_Session_Namespace('statistics');
        $statisticsListStand = $statistics->statisticsListStand;

        header('Content-type: application/csv');
        header("Content-Disposition: attachment;charset=utf-8; filename=\"{$fileName}.csv\"");
        $fp = fopen('php://output', 'w');

        $head = ['Username', 'Email', 'Views count'];
        fputcsv($fp, $head, ';');

        foreach ($statisticsListStand as $key => $statistic) {
            if ($statistic->User->getFirstName() || $statistic->User->getLastName()) {
                $username = $statistic->User->getFirstName() . ' ' . $statistic->User->getLastName();
            } else {
                $username = 'No logged in';
            }

            $fields = [
                $username,
                $statistic->User->getEmail(),
                $statistic->count,
            ];
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);
    }

    private function checkExhibStandAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->exhib_stand, 'ExhibStand NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->exhib_stand),
            [$this->getSelectedBaseUser()->getId(), $this->exhib_stand->getId()]
        );

        $this->forward403Unless(
            $this->userAuth->hasAccess(null, $this->exhib_stand)
        );
    }
}
