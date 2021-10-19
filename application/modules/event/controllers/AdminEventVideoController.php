<?php

class Event_AdminEventvideoController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Admin_EventVideo_Filter|mixed
     */
    public $filter;
    /**
     * @var \Event_Form_Admin_EventVideo|mixed
     */
    public $_eventVideoForm;
    /**
     * @var Event
     */
    private $_event;

    /**
     * @var EventVideo
     */
    private $_eventVideo;

    public function preDispatch()
    {
        parent::preDispatch();
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-event-video/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        if (!$this->getSelectedEvent()) {
            $url = $this->view->url([], 'admin_select-event') . '?return=' . $this->view->url();
            $this->_redirector->gotoUrlAndExit($url);
        }

        $this->_event = $this->getSelectedEvent();

        //Deklaracja tablicy z filtrami
        $search = ['video_name' => '', 'is_active' => ''];

        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('event_video_filter');
        } elseif (isset($search)) {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->event_video_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->event_video_filter->{$key}) ? $this->_session->event_video_filter->{$key} : $value);
            }
        }

        $this->filter = new Event_Form_Admin_EventVideo_Filter();
        $this->filter->populate($search);

        $eventVideoQuery = Doctrine_Query::create()
            ->from('EventVideo ev')
            ->leftJoin('ev.Event e')
            ->where('e.id_event = ?', $this->_event->getId())
            ->orderBy('ev.created_at ASC')
        ;

        //sprawdzenie nazwy z filtra
        if (!empty($search['video_name'])) {
            $eventVideoQuery->addWhere('ev.name LIKE ?', '%' . $search['video_name'] . '%');
        }

        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $eventVideoQuery->addWhere('ev.is_active = ?', $search['is_active']);
        }

        $pager = new Doctrine_Pager($eventVideoQuery, $this->_getParam('page', 1), 20);
        $eventVideoList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->eventVideoList = $eventVideoList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_video-list'));
        $this->view->filter = $this->filter;
    }

    public function editAction()
    {
        $this->_eventVideo = EventVideo::findOneByHash($this->_getParam('video_hash'));
        $this->_eventVideo->Event = $this->getSelectedEvent();
        $this->forward404Unless($this->_eventVideo, 'EventVideo not found, hash: (' . $this->_getParam('video_hash') . ')');
        $this->_eventVideoForm = new Event_Form_Admin_EventVideo($this->_eventVideo);

        $this->videoForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_video-edit'));
    }

    public function newAction()
    {
        $this->_eventVideo = new EventVideo();
        $this->_eventVideo->Event = $this->getSelectedEvent();

        $this->_eventVideo->id_event = $this->_eventVideo->Event->getId();
        $this->_eventVideo->hash = $this->engineUtils->getHash();
        $this->_eventVideoForm = new Event_Form_Admin_EventVideo($this->_eventVideo);
        $this->videoForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_video-new'));
    }

    public function deleteAction()
    {
        $this->_eventVideo = EventVideo::findOneByHash($this->_getParam('video_hash'));
        $this->_eventVideo->Event = $this->getSelectedEvent();
        $this->forward404Unless($this->_eventVideo, 'EventVideo not found, hash: (' . $this->_getParam('video_hash') . ')');

        $this->_eventVideo->delete();

        $this->_flash->succes->addMessage('Item has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'admin_event-video_index');
    }

    public function statusAction()
    {
        $video_hash = $this->_getParam('video_hash');

        if (!$video_hash) {
            $this->_flash->error->addMessage('EventVideo not found');
            $this->_redirector->gotoRouteAndExit([], 'admin_event-video_index');
        }
        $this->_eventVideo = EventVideo::findOneByHash($video_hash);

        $this->forward404If(false === $this->_eventVideo, 'EventVideo not exists,hash(' . $video_hash . ')');
        $this->_eventVideo->is_active = (!(bool) $this->_eventVideo->getIsActive());
        $this->_eventVideo->save();

        $this->_flash->succes->addMessage('Status changed');
        $this->_redirector->gotoRouteAndExit([], 'admin_event-video_index');
    }

    private function videoForm()
    {
        if ($this->_request->isPost() && $this->_eventVideoForm->isValid($this->_request->getPost())) {
            $this->_eventVideo->save();
            $this->_flash->succes->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit(['event_hash' => $this->_eventVideo->Event->getHash(), 'video_hash' => $this->_eventVideo->getHash()], 'admin_event-video_edit');
        }

        $this->_helper->viewRenderer('admin-event-video/edit', null, true);
        $this->view->eventVideoForm = $this->_eventVideoForm;
    }
}
