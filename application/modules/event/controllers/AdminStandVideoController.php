<?php

class Event_AdminStandvideoController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Admin_StandVideo_Filter|mixed
     */
    public $filter;
    /**
     * @var \Event_Form_Admin_StandVideo|mixed
     */
    public $_standVideoForm;
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

        $this->_exhibStand = ExhibStand::findOneByHashAndBaseUser($this->_getParam('stand_hash'), $this->getSelectedBaseUser());
        $this->forward404Unless($this->_exhibStand, 'Stand not exist, hash: (' . $this->_getParam('hash') . '), id_base_user: (' . $this->getSelectedBaseUser()->getId() . ')');
        $this->checkExhibStandAccess();

        $this->view->exhibStand = $this->_exhibStand;
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-stand/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        //Deklaracja tablicy z filtrami
        $search = ['video_name' => '', 'is_active' => ''];

        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('video_filter');
        } elseif (isset($search)) {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->video_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->video_filter->{$key}) ? $this->_session->video_filter->{$key} : $value);
            }
        }

        $this->filter = new Event_Form_Admin_StandVideo_Filter();
        $this->filter->populate($search);

        $standVideoQuery = Doctrine_Query::create()
            ->from('StandVideo sv')
            ->leftJoin('sv.ExhibStand es')
            ->where('sv.id_exhib_stand = ?', $this->_exhibStand->getId())
            ->orderBy('sv.created_at ASC')
        ;

        //sprawdzenie nazwy z filtra
        if (!empty($search['video_name'])) {
            $standVideoQuery->addWhere('sv.name LIKE ?', '%' . $search['video_name'] . '%');
        }

        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $standVideoQuery->addWhere('sv.is_active = ?', $search['is_active']);
        }

        $pager = new Doctrine_Pager($standVideoQuery, $this->_getParam('page', 1), 20);
        $standVideoList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->standVideoList = $standVideoList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_video-list'));
        $this->view->filter = $this->filter;
    }

    public function editAction()
    {
        $this->_standVideo = StandVideo::find($this->_getParam('video_hash'));
        $this->forward404Unless($this->_standVideo, 'StandVideo not found, hash: (' . $this->_getParam('video_hash') . ')');
        $this->_standVideoForm = new Event_Form_Admin_StandVideo($this->_standVideo);

        $this->videoForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_video-edit'));
    }

    public function newAction()
    {
        $this->_standVideo = new StandVideo();

        $this->_standVideo->id_exhib_stand = $this->_exhibStand->getId();
        $this->_standVideo->hash = $this->engineUtils->getHash();
        $this->_standVideoForm = new Event_Form_Admin_StandVideo($this->_standVideo);
        $this->videoForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_video-new'));
    }

    public function deleteAction()
    {
        $this->_standVideo = StandVideo::find($this->_getParam('video_hash'));
        $this->forward404Unless($this->_standVideo, 'StandVideo not found, hash: (' . $this->_getParam('video_hash') . ')');

        $this->_standVideo->delete();

        //zerujemy licznik video i tablicę z kluczami
        $this->_standVideo->ExhibStand->count_videos = null;
        $this->_standVideo->ExhibStand->map_videos = null;
        $this->_standVideo->ExhibStand->save();

        $this->_flash->succes->addMessage('Item has been deleted');
        $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'admin_stand-video_index');
    }

    public function statusAction()
    {
        $video_hash = $this->_getParam('video_hash');

        if (!$video_hash) {
            $this->_flash->error->addMessage('StandVideo not found');
            $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'admin_stand-video_index');
        }
        $standVideo = StandVideo::find($video_hash);

        $this->forward404If(false === $standVideo, 'StandVideo not exists,hash(' . $video_hash . ')');

        $standVideo->is_active = (!(bool) $standVideo->getIsActive());
        $standVideo->save();

        //zerujemy liczniki video i tablicę z kluczami
        $standVideo->ExhibStand->count_videos = null;
        $standVideo->ExhibStand->map_videos = null;
        $standVideo->ExhibStand->save();

        $this->_flash->succes->addMessage('Status changed');
        $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'admin_stand-video_index');
    }

    private function videoForm()
    {
        if ($this->_request->isPost() && $this->_standVideoForm->isValid($this->_request->getPost())) {
            $this->_standVideo->save();
            //zerujemy licznik video i tablicę z kluczami
            $this->_standVideo->ExhibStand->count_videos = null;
            $this->_standVideo->ExhibStand->map_videos = null;
            $this->_standVideo->ExhibStand->save();
            $this->_flash->succes->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit(
                ['stand_hash' => $this->_standVideo->ExhibStand->getHash(), 'video_hash' => $this->_standVideo->getHash()],
                'admin_stand-video_edit'
            );
        }

        $this->_helper->viewRenderer('admin-stand-video/edit', null, true);
        $this->view->standVideoForm = $this->_standVideoForm;
    }

    private function checkExhibStandAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_exhibStand, 'ExhibStand NOT Exists (' . $this->_getParam('stand_hash') . ')');

        $this->forward403Unless(
            $this->userAuth->hasAccess(null, $this->_exhibStand)
        );
    }
}
