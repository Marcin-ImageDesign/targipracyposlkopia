<?php

class Event_StandController extends Engine_Controller_Frontend
{
    public $exhibFile;
    /**
     * @var ExhibStand
     */
    private $exhibStand;

    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->_request->getActionName();

        if ('download-file' === $action) {
            return;
        }

        $this->view->hall_uri = $this->getParam('hall_uri', 'main_hall');
        $this->exhibStand = ExhibStand::findOneByUri($this->_getParam('stand_uri'), $this->getSelectedBaseUser(), $this->getSelectedEvent()->getId(), $this->view->hall_uri);

        $this->forward404Unless($this->exhibStand, 'Stand not exist, uri: (' . $this->_getParam('stand_uri') . '), id_base_user: (' . $this->getSelectedBaseUser()->getId() . ')');

        $this->view->rhino_url = "";
        $this->_breadcrumb[] = [
            'url' => $this->view->url([
                'event_uri' => $this->getSelectedEvent()->getUri(),
                'stand_uri' => $this->exhibStand->getUri(),
                'hall_uri' => $this->view->hall_uri,
            ], 'event_stand'),
            'label' => $this->exhibStand->getName(),
        ];

        if ('index' !== $this->_request->getActionName()) {
            $this->_breadcrumb[] = [
                'url' => $this->view->url(),
                'label' => $this->view->translate($this->addActualBreadcrumb()),
            ];
        }
        // formularz kontaktowy który zawsze wypełnia chat, podmiana odbywa się po odpytaniu chatowego api o dostępność wystawcy
        $this->addContactForm(1);
        //ustawiamy aktywną halę
        $this->setActiveEventHall($this->exhibStand->EventStandNumber->id_event_hall_map);
    }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender()) {
            $this->view->exhibStand = $this->exhibStand;
            $this->view->cookieChatAllow = true;
            if ($this->_helper->layout->isEnabled()) {
                $this->view->renderToPlaceholder('stand/_section_nav.phtml', 'section-nav-content');

                if ($this->exhibStand->isImageFbExists()) {
                    $this->view->headMeta()->setName('og:image', $this->protocol . '://' . DOMAIN . Service_Image::getUrl($this->exhibStand->id_image_fb, 220, 220));
                } else {
                    $this->view->headMeta()->setName('og:image', $this->protocol . '://' . DOMAIN . Service_Image::getUrl($this->exhibStand->id_image_logo, 220, 220));
                }

                if ($this->exhibStand->getIsMetatag()) {
                    $this->view->headMeta()->setName('og:title', $this->exhibStand->getMetatagTitle());
                    $this->view->headMeta()->setName('og:description', $this->exhibStand->getMetatagDesc());
                    $this->view->headMeta()->setName('description', $this->exhibStand->getMetatagDesc());
                    $this->view->headMeta()->setName('keywords', $this->exhibStand->getMetatagKey());
                    $this->view->headTitle($this->exhibStand->getMetatagTitle());
                }
            }
        }
    }

    public function previewAction()
    {
        $this->_helper->layout->setLayout('layout_empty');
        $this->view->id_briefcase_type = BriefcaseType::TYPE_BRIEFCASE_STAND;
        $data_stand = $this->exhibStand->ExhibStandViewImage->getDataStand();
        $hostess_data_map = $this->exhibStand->relatedExists('ExhibStandHostess') ? $this->exhibStand->ExhibStandHostess->getConfig() : [];

        if (isset($hostess_data_map['change']['hostess'])) {
            $data_stand['hostess']['x'] += $hostess_data_map['change']['hostess']['x'];
            $data_stand['hostess']['y'] += $hostess_data_map['change']['hostess']['y'];
        } elseif (isset($hostess_data_map['set']['hostess'])) {
            $data_stand['hostess']['x'] = $hostess_data_map['set']['hostess']['x'];
            $data_stand['hostess']['y'] = $hostess_data_map['set']['hostess']['y'];
        }

        $this->view->data_stand = $data_stand;
        $this->view->selectedAction = $this->_request->getActionName();
    }

    public function indexAction()
    {
        //metatagi
        $this->view->headMeta()->setName('description', $this->exhibStand->getName() . ' - ' . $this->exhibStand->getShortInfo());
        $this->view->headMeta()->setName('og:title', $this->exhibStand->getName());
        $this->view->headMeta()->setName('og:description', $this->exhibStand->getName() . ' - ' . $this->exhibStand->getShortInfo());
        $this->view->headTitle($this->exhibStand->getName());

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_VIEW, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());

        $hostess_data_map = $this->exhibStand->relatedExists('ExhibStandHostess') ? $this->exhibStand->ExhibStandHostess->getConfig() : [];

        $groupStatusList = [];
        $this->view->isOperatorOnline = isset($groupStatusList[$this->exhibStand->live_chat_group_id]);
        $this->view->id_briefcase_type = BriefcaseType::TYPE_BRIEFCASE_STAND;
        //dane kontaktu
        $this->view->contentContact = $this->exhibStand->getContactInfo();

        $data_stand = $this->exhibStand->ExhibStandViewImage->getDataStand();

        if (isset($hostess_data_map['change']['hostess'])) {
            $data_stand['hostess']['x'] += $hostess_data_map['change']['hostess']['x'];
            $data_stand['hostess']['y'] += $hostess_data_map['change']['hostess']['y'];
        } elseif (isset($hostess_data_map['set']['hostess'])) {
            $data_stand['hostess']['x'] = $hostess_data_map['set']['hostess']['x'];
            $data_stand['hostess']['y'] = $hostess_data_map['set']['hostess']['y'];
        }

        $this->view->data_stand = $data_stand;
        $this->view->hostess_data_map = $hostess_data_map;
    }

    public function infoAction()
    {
        $this->view->content = $this->exhibStand->getExhibitorInfo();
    }

    public function contactAction()
    {
        $this->addInquiryContactForm();
        $this->view->is_additional_contact_on = Engine_Variable::getInstance()->getVariable(Variable::ADDITIONAL_CONTACT_FIELD_ON);
        $this->view->content = $this->exhibStand->getContactInfo();
    }

    public function contactchatAction()
    {
        $this->addInquiryContactForm(1);
        $this->view->content = $this->exhibStand->getContactInfo();
    }

    public function facebookAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->exhibStand->isFbAddress()) {
            // Statystyki
            Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_FACEBOOK_VIEW, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());
            // przekierowanie na stronê facebook
            $this->_helper->redirector->gotoUrl($this->exhibStand->getFbAddress());
        }
    }

    public function shareFacebookAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Statystyki
        // Sprawdzam czy punkty zostały juz naliczone. Jeśli nie to naliczam
        if (!Statistics::checkEntry(Statistics::CHANNEL_STAND_FACEBOOK_SHARE, $this->exhibStand->getId(), $this->getSelectedEvent()->getId())) {
            Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_FACEBOOK_SHARE, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());
        }
    }

    public function skypeAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->exhibStand->isSkypeName()) {
            // Statystyki

            Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_SKYPE_CLICK, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());

            // przekierowanie do kontaktu skype
            header('Location: ' . $this->exhibStand->getSkypeNameAdress());
            // var_dump($this->exhibStand->getSkypeNameAdress());
            die();
        }
    }

    public function shareGoogleplusAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Statystyki
        // Sprawdzam czy punkty zostały juz naliczone. Jeśli nie to naliczam
        if (!Statistics::checkEntry(Statistics::CHANNEL_STAND_GOOGLEPLUS_SHARE, $this->exhibStand->getId(), $this->getSelectedEvent()->getId())) {
            Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_GOOGLEPLUS_SHARE, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());
        }
    }

    public function shareTwitterAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Statystyki
        // Sprawdzam czy punkty zostały juz naliczone. Jeśli nie to naliczam
        if (!Statistics::checkEntry(Statistics::CHANNEL_STAND_TWITTER_SHARE, $this->exhibStand->getId(), $this->getSelectedEvent()->getId())) {
            Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_TWITTER_SHARE, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());
        }
    }

    public function filesAction()
    {
        $this->view->id_briefcase_type = BriefcaseType::TYPE_BRIEFCASE_DOCUMENT;

        $this->view->exhibStandFile = Doctrine_Query::create()
            ->from('ExhibStandFile sf')
            ->where('sf.id_exhib_stand = ?', $this->exhibStand->getId())
            ->addWhere('sf.is_visible = 1')
            ->orderBy('sf.name ASC')
            ->execute()
        ;
    }

    public function chatAction()
    {
        if (!$this->exhibStand->is_active_chat) {
            $this->_redirector->gotoRouteAndExit(
                ['event_uri' => $this->getSelectedEvent()->getUri(), 'hall_uri' => $this->_getParam('hall_uri'), 'stand_uri' => $this->exhibStand->getUri()],
                'event_stand'
            );
        }

        if (empty($this->exhibStand->id_chat_room)) {
            $this->exhibStand->ChatRoom = new ChatRoom();
            $this->exhibStand->ChatRoom->hash = Engine_Utils::_()->getHash();
            $this->exhibStand->ChatRoom->BaseUser = $this->exhibStand->BaseUser;
            $this->exhibStand->ChatRoom->is_active = false;
            $this->exhibStand->ChatRoom->title = $this->exhibStand->getName();
            $this->exhibStand->ChatRoom->is_public = false;
            $this->exhibStand->save();
        }
    }

    public function wwwSiteAction()
    {
        Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_WWW_VIEW, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());

        $url = 'http://' . DOMAIN . $this->view->url(['event_uri' => $this->getSelectedEvent()->getUri(), 'hall_uri' => $this->_getParam('hall_uri'), 'stand_uri' => $this->exhibStand->getUri()], 'event_stand');
        $www = $this->exhibStand->getWwwAdress();
        if (!empty($www)) {
            $url = 'http://' . str_replace('http://', '', $www);
        }

        $this->_redirector->gotoUrlAndExit($url);

//        $this->wwwForm = new Event_Form_Stand_WwwSite(array(
//            'baseUser' => $this->getSelectedBaseUser(),
//            'exhibStand' => $this->exhibStand
//        ));
//
//        if($this->_request->isPost()){
//            if($this->wwwForm->isValid($this->_request->getPost())){
//                    $this->jsonResult['messageType'] = 'html';
//                    $this->jsonResult['message'] = '<span>'.$this->view->translate('message_stand-form-www').'</span><br /><div style="text-align:center"><a class="redirLink" style="font-weight:bold;color:#000" onclick="jumpToSite()" href="http://'.$this->exhibStand->getWwwAdress().'" target="_blank">'.$this->exhibStand->getWwwAdress().'</a></div>';
//                    $this->jsonResult['content_id'] = $this->wwwForm->getElementsBelongTo().'Form';
//            }else {
//                $this->jsonResult['messageType'] = 'html';
//                $this->jsonResult['message'] = $this->wwwForm->render();
//                $this->jsonResult['content_id'] = $this->wwwForm->getElementsBelongTo().'Form';
//            }
//        }else{
//             Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_WWW_VIEW, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());
//        }
//
//        $this->view->wwwForm = $this->wwwForm;
//        $this->view->exhibStand = $this->exhibStand;
    }

    public function downloadFileAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->exhibFile = ExhibStandFile::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser(), $this->getSelectedLanguage());
        $filename = $this->exhibFile->uri . '.' . $this->exhibFile->file_ext;
        $file_path = $this->exhibFile->getDownloadFile();

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_FILE_VIEW, $this->exhibFile->getIdExhibStand(), $this->exhibFile->ExhibStand->id_event, [$this->exhibFile->getId()]);

        if ($this->exhibFile->is_visible && !empty($file_path) && file_exists($file_path)) {
            // Statystyki pobierania plików
//            $this->addDownloadFileStatistics('stand');

            header('Cache-control: private');
            header('Content-Type: ' . Engine_File::getFileMime($filename));
            header('Content-Length: ' . filesize($file_path));
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($file_path);
            exit();
        }
        echo 'The specified file does not exist : ' . $filename;
    }

    private function addContactForm($is_chat = 0)
    {
        $formContact = new Event_Form_Stand_Inquiry_Contact([
            'baseUser' => $this->getSelectedBaseUser(),
            'user' => $this->userAuth,
            'exhibStand' => $this->exhibStand,
        ]);
        $is_chat && $formContact->setAttrib('id', $formContact->getElementsBelongTo() . 'Form_Chat');
        $is_chat && $formContact->setAction($this->view->url(['event_uri' => $this->getSelectedEvent()->getUri(), 'hall_uri' => $this->_getParam('hall_uri'), 'stand_uri' => $this->exhibStand->getUri()], 'event_stand_contact_chat'));
        $this->view->formContact = $formContact;
    }

    private function addInquiryContactForm($is_chat = 0)
    {
        $this->addContactForm($is_chat);

        if ($this->getRequest()->isPost()) {
            if ($this->view->formContact->isValid($this->getRequest()->getPost())) {
                $delay = Engine_Variable::getInstance()->getVariable(Variable::GAMIFICATION_DELAY);
                if (!Statistics_Service_Manager::checkIfAllowed(Statistics::CHANNEL_STAND_CONTACT_VIEW, $this->getSelectedEvent()->getId(), $delay)) {
                    if ($this->displayJsonResponse) {
                        $this->jsonResult['messageType'] = 'html';
                        $this->jsonResult['message'] = '<div class="message"><div class="message-warning">' .
                            $this->view->translate('message_stand-inquery-contact_delay') .
                            '</div></div>';
                        $this->jsonResult['content_id'] = $this->view->formContact->getElementsBelongTo() . 'Form' . ($is_chat ? '_Chat' : '');
                    } else {
                        $this->view->formContact = $this->view->translate('message_stand-inquery-contact_delay');
                    }
                } else {
                    // Statystyki
                    Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_CONTACT_VIEW, $this->exhibStand->getId(), $this->getSelectedEvent()->getId());

                    $url = $this->view->url(
                        ['event_uri' => $this->getSelectedEvent()->getUri(), 'stand_uri' => $this->exhibStand->getUri()],
                        'event_stand_contact' . ($is_chat ? '_chat' : '')
                    );

                    if ($this->displayJsonResponse) {
                        $this->jsonResult['messageType'] = 'html';
                        $this->jsonResult['message'] = '<div class="message"><div class="message-success">' .
                            $this->view->translate('message_stand-inquery-contact_send') .
                            '</div></div>';
                        $this->jsonResult['content_id'] = $this->view->formContact->getElementsBelongTo() . 'Form' . ($is_chat ? '_Chat' : '');
                    } else {
                        $this->_redirector->gotoUrlAndExit($url);
                    }
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $this->view->formContact->render();
                $this->jsonResult['content_id'] = $this->view->formContact->getElementsBelongTo() . 'Form' . ($is_chat ? '_Chat' : '');
            }
        }
    }
}
