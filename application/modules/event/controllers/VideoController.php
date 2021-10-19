<?php

class Event_VideoController extends Engine_Controller_Frontend
{
    /**
     * @var Event
     */
    private $_event;

    /**
     * @var StandVideo
     */
    private $_eventVideo;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_event = Event::findOneByUri($this->_getParam('event_uri'));
        $this->forward404Unless($this->_event, 'Event not exist, uri: (' . $this->_getParam('event_uri') . '), id_base_user: (' . $this->getSelectedBaseUser()->getId() . ')');
    }

    public function postDispatch()
    {
        parent::postDispatch();
    }

    public function indexAction()
    {
        $eventVideoQuery = Doctrine_Query::create()
            ->from('EventVideo ev')
            ->innerJoin('ev.Translations t')
            ->where('ev.is_active = 1 AND ev.id_event = ?', $this->_event->getId())
            ->addWhere('t.id_language = ?', Engine_I18n::getLangId())
            ->orderBy('ev.created_at ASC')
        ;

        $pager = new Doctrine_Pager($eventVideoQuery, $this->_getParam('page', 1), 12);
        $eventVideoList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->eventVideoList = $eventVideoList;
    }

    public function detailsAction()
    {
        $this->_eventVideo = EventVideo::findOneByHash($this->_getParam('video_hash'));
        $this->forward404Unless($this->_eventVideo, 'EventVideo not found, hash: (' . $this->_getParam('video_hash') . ')');

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_RECEPTION_VIDEO_VIEW, $this->_eventVideo->getId(), $this->getSelectedEvent()->getId());

        $this->view->eventVideo = $this->_eventVideo;
    }
}
