<?php

class Event_StandVideoController extends Engine_Controller_Frontend
{
    /**
     * @var ExhibStand
     */
    private $_exhibStand;

    /**
     * @var StandVideo
     */
    private $_standVideo;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_exhibStand = ExhibStand::findOneByUri($this->_getParam('stand_uri'), $this->getSelectedBaseUser(), $this->getSelectedEvent()->getId(), $this->_getParam('hall_uri'));
        $this->forward404Unless($this->_exhibStand, 'Stand not exist, uri: (' . $this->_getParam('stand_uri') . '), id_base_user: (' . $this->getSelectedBaseUser()->getId() . ')');
        $this->view->hall_uri = $this->getParam('hall_uri', 'main_hall');

        $this->_breadcrumb[] = [
            'url' => $this->view->url([
                'event_uri' => $this->getSelectedEvent()->getUri(),
                'stand_uri' => $this->_exhibStand->getUri(),
                'hall_uri' => $this->view->hall_uri,
            ], 'event_stand'),
            'label' => $this->_exhibStand->getName(),
        ];

        $this->_breadcrumb[] = [
            'url' => $this->view->url([
                'event_uri' => $this->getSelectedEvent()->getUri(),
                'stand_uri' => $this->_exhibStand->getUri(),
                'hall_uri' => $this->view->hall_uri,
            ], 'event_stand-video_index'),
            'label' => $this->view->translate('breadcrumb_video-list'),
        ];

        // RHINO  TODO REMOVE
        $this->view->rhino_url = '';
        $this->addContactForm(1);
        //ustawiamy aktywną halę
        $this->setActiveEventHall($this->_exhibStand->EventStandNumber->id_event_hall_map);
    }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender()) {
            $this->view->exhibStand = $this->_exhibStand;
            $this->view->cookieChatAllow = true;
            if ($this->_helper->layout->isEnabled()) {
                $this->view->renderToPlaceholder('stand/_section_nav.phtml', 'section-nav-content');
            }
        }
    }

    public function indexAction()
    {
        $standVideoQuery = Doctrine_Query::create()
            ->from('StandVideo sv')
            ->innerJoin('sv.Translations t')
            ->where('sv.is_active = 1 AND sv.id_exhib_stand = ?', $this->_exhibStand->getId())
            ->addWhere('t.id_language = ?', Engine_I18n::getLangId())
            ->orderBy('sv.created_at ASC')
        ;

        $pager = new Doctrine_Pager($standVideoQuery, $this->_getParam('page', 1), 10);
        $standVideoList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->standVideoList = $standVideoList;
    }

    public function detailsAction()
    {
        $this->_standVideo = StandVideo::find($this->_getParam('video_hash'));
        $this->forward404Unless($this->_standVideo, 'StandVideo not found, hash: (' . $this->_getParam('video_hash') . ')');

        $this->view->standVideo = $this->_standVideo;

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_VIDEO_VIEW, $this->_exhibStand->getId(), $this->getSelectedEvent()->getId(), [$this->_standVideo->getId()], $this->_standVideo->getId());

        $this->_breadcrumb[] = [
            'url' => $this->view->url(),
            'label' => $this->view->escape($this->_standVideo->getName()),
        ];
    }

    private function addContactForm($is_chat = 0)
    {
        $formContact = new Event_Form_Stand_Inquiry_Contact([
            'baseUser' => $this->getSelectedBaseUser(),
            'user' => $this->userAuth,
            'exhibStand' => $this->_exhibStand,
        ]);
        $is_chat && $formContact->setAttrib('id', $formContact->getElementsBelongTo() . 'Form_Chat');
        $is_chat && $formContact->setAction($this->view->url(['event_uri' => $this->getSelectedEvent()->getUri(),
            'stand_uri' => $this->_exhibStand->getUri(),
            'hall_uri' => $this->view->hall_uri, ], 'event_stand_contact_chat'));
        $this->view->formContact = $formContact;
    }
}
