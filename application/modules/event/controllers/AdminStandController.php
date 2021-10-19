<?php

class Event_AdminStandController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Admin_Filter|mixed
     */
    public $filter;
    /**
     * @var ExhibStand
     */
    private $_exhibStand;

    /**
     * @var Event_Form_Admin_Stand
     */
    private $_formStand;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin-stand/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        //Deklaracja tablicy z filtrami
        $search = ['stand_number' => '', 'stand_name' => '', 'stand_type' => '', 'id_event_hall_map' => '', 'is_active' => ''];

        if ($this->hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('stand_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->stand_filter->{$key} = $this->hasParam($key)
                    ? trim($this->_getParam($key, $value))
                    : (isset($this->_session->stand_filter->{$key})
                        ? $this->_session->stand_filter->{$key} : $value);
            }
        }

        // deklaracja formularza event/form/admin/Filter.php
        $this->filter = new Event_Form_Admin_Filter(['event' => $this->getSelectedEvent()]);
        $this->filter->populate($search);

        $standQuery = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->leftJoin('sp.ExhibParticipation p')
            ->leftJoin('p.Event e')
            ->leftJoin('s.EventStandNumber esn')
            ->leftJoin('esn.EventHallMap ehm')
            ->where('s.id_exhib_stand_type = ? AND s.id_base_user = ?', [ExhibStandType::STANDARD, $this->getSelectedBaseUser()->getId()])
            ->orderby('ehm.id_event_hall_map ASC, s.id_stand_level DESC, t.name ASC')
        ;

        if ($this->hasSelectedEvent()) {
            $standQuery->addWhere('s.id_event = ?', $this->getSelectedEvent()->getId());
        }

        if (!$this->userAuth->isAdmin() && !$this->userAuth->isOrganizer()) {
            $standQuery->addWhere('p.id_user = ?', $this->userAuth->getId());
        }

        //sprawdzenie numeru stoiska z filtra
        if (!empty($search['stand_number'])) {
            $standQuery->addWhere('esn.name LIKE ?', '%' . $search['stand_number'] . '%');
        }
        //sprawdzenie nazwy z filtra
        if (!empty($search['stand_name'])) {
            $standQuery->addWhere('t.name LIKE ?', '%' . $search['stand_name'] . '%');
        }
        //sprawdzamy typ stoiska z filtra
        if (!empty($search['stand_type'])) {
            $standQuery->addWhere('s.id_stand_level = ?', $search['stand_type']);
        }

        //sprawdzamy halę z filtra
        if (!empty($search['id_event_hall_map'])) {
            $standQuery->addWhere('ehm.id_event_hall_map = ?', $search['id_event_hall_map']);
        }
        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $standQuery->addWhere('s.is_active = ?', $search['is_active']);
        }

        $pager = new Doctrine_Pager($standQuery, $this->_getParam('page', 1), 20);
        $standList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_stands-list'));
        $this->view->standList = $standList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->filter = $this->filter;
    }

    public function newAction()
    {
        if (!$this->hasSelectedEvent()) {
            $this->_flash->error->addMessage($this->view->translate('error_select_event_first'));
            $this->_redirector->gotoRouteAndExit([], 'event_admin-stand_index');
        }

        $this->_exhibStand = new ExhibStand();
        $this->_exhibStand->BaseUser = $this->getSelectedBaseUser();
        $this->_exhibStand->hash = $this->engineUtils->getHash();
        $this->_exhibStand->id_exhib_stand_participation = ExhibStandType::STANDARD;
        $this->_exhibStand->id_stand_level = 1;
        $this->_exhibStand->is_owner_view = false;

        if ($this->hasSelectedEvent()) {
            $this->_exhibStand->Event = $this->getSelectedEvent();
        }

        $this->_formStand = new Event_Form_Admin_Stand_New($this->_exhibStand, $this->getSelectedLanguage());
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_stand_new'));
        $this->formStand();
    }

    public function editAction()
    {
        $this->_exhibStand = ExhibStand::findOneByHash(
            $this->_getParam('stand_hash'),
            $this->getSelectedBaseUser(),
            $this->getSelectedLanguage()
        );
        $this->checkExhibStandAccess();
        $this->_formStand = new Event_Form_Admin_Stand_Edit($this->_exhibStand, $this->getSelectedLanguage(), $this->userAuth);

        if (!$this->userAuth->isAdmin() && !$this->userAuth->isOrganizer()) {
            $this->_formStand->prepareFormToExhibitor();
        }

        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-page-title_stand_edit'));
        $this->formStand();
    }

    public function standViewFileDeleteAction()
    {
        $this->_exhibStand = ExhibStand::findOneByHash(
            $this->_getParam('stand_hash'),
            $this->getSelectedBaseUser(),
            $this->getSelectedLanguage()
        );
        $this->forward404Unless($this->_exhibStand, 'ExhibStand not found hash: (' . $this->_getParam('stand_hash') . ')');

        //pobranie elementu po polu hash
        $stand_view_file_type = $this->_getParam('stand_view_file_type');
        //sprawdzenie dostępu

        if (!empty($stand_view_file_type)) {
            //usuwanie pliku
            $this->_exhibStand->deleteStandViewFile($stand_view_file_type);
            //usuwanie recordu w db
            $this->_exhibStand->save();

            $this->_flash->succes->addMessage($this->view->translate('cms_message_delete_success'));
        }
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand_edit');
    }

    public function standStatusAction()
    {
        //pobranie elemntu po polu hash
        $this->_exhibStand = ExhibStand::findOneByHash($this->_getParam('stand_hash'), $this->getSelectedBaseUser());
        //sprawdzenie dostępu do elementu
        $this->checkExhibStandAccess();
        //zmiana statusu
        $this->_exhibStand->is_active = $this->_exhibStand->is_active ? 0 : 1;
        // var_dump($this->_exhibStand->is_active);die;
        //zapis do bazy
        $this->_exhibStand->save();
        //przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'event_admin-stand_index');
    }

    public function generateNumberAction()
    {
//        for($i=1; $i<=110; $i++){
//            $stand_level = StandLevel::LEVEL_STANDARD;
//            if($i<=28){
//                $stand_level = StandLevel::LEVEL_REGIONAL;
//                if($i<=2){
//                    $stand_level = StandLevel::LEVEL_MAIN;
//                }
//            }
//
//            $eventStandNumber = new EventStandNumber();
//            $eventStandNumber->id_event_hall_map = 8;
//            $eventStandNumber->id_stand_level = $stand_level;
//            $eventStandNumber->hash = Engine_Utils::_()->getHash();
//            $eventStandNumber->is_active = 1;
//            $eventStandNumber->number = $i;
//            $eventStandNumber->name = $i;
//            $eventStandNumber->save();
//        }
//
//        exit();

        $numbers = array_fill(643, 122, ['x' => 830, 'y' => 650]);
        $numbChange = [
            '644' => ['ch_x' => 240, 'ch_y' => -110],
            '646' => ['ch_x' => -70, 'ch_y' => -60],
            '647' => ['ch_x' => -220, 'ch_y' => 95],
            '649' => ['ch_x' => -130, 'ch_y' => -70],
            '650' => ['ch_x' => 220, 'ch_y' => -98],
            '652' => ['ch_x' => -60, 'ch_y' => 500],
            '653' => ['ch_x' => 240, 'ch_y' => -120],
            '655' => ['ch_x' => -1520, 'ch_y' => 230],
            '656' => ['ch_x' => 85, 'ch_y' => 100],
            '657' => ['ch_x' => 76, 'ch_y' => 80],
            '662' => ['ch_x' => 85, 'ch_y' => 90],
            '664' => ['ch_x' => 100, 'ch_y' => 110],
            '665' => ['ch_x' => -570, 'ch_y' => -840],
            '666' => ['ch_x' => 85, 'ch_y' => 90],
            '667' => ['ch_x' => 76, 'ch_y' => 80],
            '668' => ['ch_x' => 70, 'ch_y' => 76],
            '672' => ['ch_x' => 90, 'ch_y' => 95],
            '674' => ['ch_x' => 100, 'ch_y' => 110],
            '675' => ['ch_x' => -600, 'ch_y' => -840],
            '676' => ['ch_x' => 85, 'ch_y' => 90],
            '677' => ['ch_x' => 76, 'ch_y' => 85],
            '678' => ['ch_x' => 70, 'ch_y' => 76],
            '679' => ['ch_x' => 140, 'ch_y' => 120],
            '680' => ['ch_x' => 85, 'ch_y' => 90],
            '681' => ['ch_x' => 95, 'ch_y' => 100],
            '682' => ['ch_x' => 85, 'ch_y' => 90],
            '683' => ['ch_x' => 105, 'ch_y' => 110],
            '684' => ['ch_x' => 100, 'ch_y' => 110],
            '685' => ['ch_x' => -700, 'ch_y' => -940],
            '686' => ['ch_x' => 85, 'ch_y' => 90],
            '687' => ['ch_x' => 76, 'ch_y' => 85],
            '688' => ['ch_x' => 70, 'ch_y' => 76],
            '689' => ['ch_x' => 140, 'ch_y' => 120],
            '690' => ['ch_x' => 85, 'ch_y' => 90],
            '691' => ['ch_x' => 95, 'ch_y' => 100],
            '692' => ['ch_x' => 85, 'ch_y' => 90],
            '693' => ['ch_x' => 105, 'ch_y' => 110],
            '694' => ['ch_x' => 90, 'ch_y' => 90],
            '695' => ['ch_x' => -180, 'ch_y' => -480],
            '696' => ['ch_x' => 95, 'ch_y' => 90],
            '698' => ['ch_x' => 100, 'ch_y' => 90],
            '700' => ['ch_x' => -280, 'ch_y' => -430],
            '701' => ['ch_x' => 95, 'ch_y' => 90],
            '702' => ['ch_x' => 100, 'ch_y' => 90],
            '705' => ['ch_x' => -270, 'ch_y' => -430],
            '706' => ['ch_x' => 100, 'ch_y' => 95],
            '708' => ['ch_x' => 120, 'ch_y' => 85],
            '709' => ['ch_x' => 95, 'ch_y' => 85],
            '710' => ['ch_x' => -310, 'ch_y' => -400],
            '711' => ['ch_x' => 105, 'ch_y' => 95],
            '712' => ['ch_x' => 100, 'ch_y' => 80],
            '713' => ['ch_x' => 120, 'ch_y' => 85],
            '714' => ['ch_x' => 95, 'ch_y' => 85],
            '715' => ['ch_x' => -290, 'ch_y' => -400],
            '716' => ['ch_x' => 105, 'ch_y' => 80],
            '717' => ['ch_x' => 95, 'ch_y' => 70],
            '718' => ['ch_x' => 115, 'ch_y' => 85],
            '719' => ['ch_x' => 95, 'ch_y' => 75],
            '720' => ['ch_x' => -310, 'ch_y' => -370],
            '721' => ['ch_x' => 105, 'ch_y' => 80],
            '722' => ['ch_x' => 95, 'ch_y' => 70],
            '723' => ['ch_x' => 115, 'ch_y' => 85],
            '724' => ['ch_x' => 95, 'ch_y' => 75],
            '725' => ['ch_x' => -760, 'ch_y' => -750],
            '726' => ['ch_x' => 90, 'ch_y' => 65],
            '729' => ['ch_x' => 140, 'ch_y' => 95],
            '730' => ['ch_x' => 107, 'ch_y' => 75],
            '735' => ['ch_x' => -820, 'ch_y' => -720],
            '736' => ['ch_x' => 90, 'ch_y' => 65],
            '739' => ['ch_x' => 140, 'ch_y' => 95],
            '740' => ['ch_x' => 107, 'ch_y' => 75],
            '745' => ['ch_x' => -840, 'ch_y' => -700],
            '746' => ['ch_x' => 85, 'ch_y' => 57],
            '749' => ['ch_x' => 85, 'ch_y' => 60],
            '752' => ['ch_x' => 100, 'ch_y' => 65],
            '754' => ['ch_x' => 125, 'ch_y' => 85],
            '755' => ['ch_x' => -710, 'ch_y' => -620],
            '756' => ['ch_x' => 86, 'ch_y' => 56],
            '762' => ['ch_x' => 90, 'ch_y' => 75],
            '763' => ['ch_x' => 90, 'ch_y' => 65],
        ];

//        var_dump($numbers);
//        var_dump($numbChange);
//        exit();

        $x = $numbers['643']['x'];
        $y = $numbers['643']['y'];
        foreach ($numbers as $v => &$k) {
            $k['x'] = $x;
            $k['y'] = $y;

            $ch_x = isset($numbChange[$v + 1]['ch_x']) ? $numbChange[$v + 1]['ch_x'] : $ch_x;
            $ch_y = isset($numbChange[$v + 1]['ch_y']) ? $numbChange[$v + 1]['ch_y'] : $ch_y;

            $x += $ch_x;
            $y += $ch_y;
        }

//        var_dump($numbers);
//        exit();

        foreach ($numbers as $k => $v) {
            $eventStandNumber = EventStandNumber::findOneByIdAndEventHallMap($k, 10);
//            if ( !$eventStandNumber ) {
//                var_dump( $v, $k );
//                exit;
//            }
            $eventStandNumber->logo_pos_x = $v['x'];
            $eventStandNumber->logo_pos_y = $v['y'];
            $eventStandNumber->save();
        }

        exit();
    }

    public function previewStandIframeAction()
    {
        $this->view->exhibStandUri = $this->_getParam('stand_uri');
        $this->view->exhibEventUri = $this->_getParam('event_uri');
        $this->view->hall_uri = $this->_getParam('hall_uri');
        $this->_helper->layout->disableLayout();
    }

    public function previewHallStandsIframeAction()
    {
        $this->view->eventHash = $this->_getParam('event_hash');
        $this->view->hall_uri = $this->_getParam('hall_uri');
        $this->view->is_template = $this->_getParam('is_template');
        $this->_helper->layout->disableLayout();
    }

    public function deleteAction()
    {
        $stand = ExhibStand::findOneByHash($this->_getParam('hash'));

        $this->forward404Unless($stand, 'Stand NOT Exists (' . $this->_getParam('hash') . ')');

        $stand->delete();

        $this->_flash->succes->addMessage($this->view->translate('cms_message_delete_success'));
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'event_admin-stand_index');
    }

    public function cloneAction()
    {
        $this->_exhibStand = ExhibStand::findOneByHash($this->_getParam('stand_hash'));

        $this->forward404Unless($this->_exhibStand, 'Stand NOT Exists (' . $this->_getParam('stand_hash') . ')');

        $cloneForm = new Event_Form_Admin_Stand_Clone($this->_exhibStand);

        if ($this->_request->isPost() && $cloneForm->isValid($this->_request->getPost())) {
            $new_hall_id = $cloneForm->event_hall_map->getValue();
            $new_stand_number = $cloneForm->id_event_stand_number->getValue();
            //klonujemy stoisko
            $newStand = ExhibStand::cloneStand($this->_exhibStand, $new_stand_number, $new_hall_id);
            //klonujemy uczestnictwo
            foreach ($this->_exhibStand->ExhibStandParticipation as $participation) {
                $clone = new ExhibStandParticipation();
                $clone->hash = $this->engineUtils->getHash();
                $clone->UserCreated = $this->userAuth;
                $clone->ExhibParticipation = $participation->ExhibParticipation;
                $newStand->ExhibStandParticipation->add($clone);
            }
            //klonujemy video
            foreach ($this->_exhibStand->StandVideo as $video) {
                $clone = new StandVideo();
                $clone->id_exhib_stand = $newStand->getId();
                $clone->hash = $this->engineUtils->getHash();
                $clone->setName($video->getName());
                $clone->setLead($video->getLead());
                $clone->setVideoLink($video->getVideoLink());
                $clone->setIsActive($video->getIsActive());
                $clone->setShowOnStand($video->getShowOnStand());
                $newStand->StandVideo->add($clone);
            }
            //klonujemy pliki
            foreach ($this->_exhibStand->ExhibStandFile as $file) {
                $clone = new ExhibStandFile();
                $clone->ExhibStand = $newStand;
                $clone->BaseUser = $this->getSelectedBaseUser();
                $clone->hash = $this->engineUtils->getHash();
                $clone->CreatorUser = $file->CreatorUser;
                $clone->setName($file->getName());
                $clone->setDescription($file->getDescription());
                $clone->setFileExt($file->getFileExt());
                $clone->setUri($file->getUri());
                $clone->setIdExhibStand($newStand->getId());
                $clone->setIdUserCreated($file->getIdUserCreated());
                $clone->setIsVisible($file->getIsVisible());
                $clone->setImageExt($file->getImageExt());
                $clone->save();
                $newStand->ExhibStandFile->add($clone);
                //fizycznie kopiujemy plik
                copy($file->getBrowserFile(), $file->getRelativePath() . DS . $clone->getId() . '.' . $file->getFileExt());
                //jeśli jest, kopiujemy okładkę
                if ($file->getImageExt()) {
                    copy($file->getAbsoluteImagePath() . DS . 'image_' . $file->getHash() . '.' . $file->getImageExt(), $clone->getAbsoluteImagePath() . DS . 'image_' . $clone->getHash() . '.' . $clone->getImageExt());
                }
            }
            $newStand->save();
            if ($newStand->getId()) {
                $this->_flash->success->addMessage($this->view->translate('Clonning sucess!'));
                $this->_redirector->gotoRouteAndExit(['stand_hash' => $newStand->getHash()], 'event_admin-stand_edit');
            } else {
                $this->_flash->error->addMessage($this->view->translate('Clonning failed!'));
                $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand_edit');
            }
        }

        $this->_helper->layout->disableLayout();
        $this->view->cloneForm = $cloneForm;
        $this->view->exhibStand = $this->_exhibStand;
    }

    private function formStand()
    {
        if ($this->_request->isPost() && $this->_formStand->isValid($this->_request->getPost())) {
            $this->standGetRequest();

            //generujemy uri
            $this->_exhibStand->setUri(Engine_Utils::_()->getFriendlyUri($this->_exhibStand->getName()));
            $this->_exhibStand->save();

            $this->_flash->succes->addMessage($this->view->translate('cms_message_save_success'));
            $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand_edit');
        }

        $this->_helper->viewRenderer('form');
        $this->view->exhibStand = $this->_exhibStand;
        $this->view->form = $this->_formStand;
    }

    private function standGetRequest()
    {
        // POZOSTAŁE ŚMIECI DO USUNIĘCIA
        $this->_formStand->populateBrand();

        if (in_array((int)$this->userAuth->UserRole->getId(), [UserRole::ROLE_ADMIN, UserRole::ROLE_ORGANIZER], true)) {
            if ($this->_exhibStand->isNew()) {
                $this->_exhibStand->id_event = $this->_formStand->id_event->getValue();
            }
            $participation_hashes = $this->_formStand->participation->getValue();
            //dezaktywacja wszystkich uczestnictw dla danego stoiska
            foreach ($this->_exhibStand->ExhibStandParticipation as $ExhibStandParticipation) {
                if (!in_array($ExhibStandParticipation->ExhibParticipation->getHash(), $participation_hashes, true)) {
                    $ExhibStandParticipation->is_active = false;
                }

                if (false !== ($key = array_search($ExhibStandParticipation->ExhibParticipation->getHash(), $participation_hashes, true))) {
                    unset($participation_hashes[$key]);
                }
            }
            if (!empty($participation_hashes)) {
                foreach ($participation_hashes as $participation_hash) {
                    $participation = ExhibParticipation::findOneByHash($participation_hash);
                    $this->forward403Unless(
                        $this->getSelectedBaseUser()->hasAccess($participation),
                        'Forbidden BaseUser (' . $this->getSelectedBaseUser()->getId() . ') to ExhibParticipation (' . $participation->getId() . ')'
                    );

                    $this->getSelectedBaseUser()->hasAccess($participation);

                    $ExhibStandParticipation = new ExhibStandParticipation();
                    $ExhibStandParticipation->hash = $this->engineUtils->getHash();
                    $ExhibStandParticipation->UserCreated = $this->userAuth;
                    $ExhibStandParticipation->ExhibParticipation = $participation;
                    $this->_exhibStand->ExhibStandParticipation[] = $ExhibStandParticipation;
                }
            }

            $this->_exhibStand->is_active = (bool) $this->_formStand->is_active->getValue();
            $this->_exhibStand->is_contact_active = (bool) $this->_formStand->is_contact_active->getValue();
        }

        $this->_exhibStand->setName($this->_formStand->name->getValue());

        if (Engine_Variable::getInstance()->getVariable(Variable::ADDITIONAL_CONTACT_FIELD_ON)) {
            $this->_exhibStand->setContactInfo($this->_formStand->contact_info->getValue());
        }
        $this->_exhibStand->is_active_chat = (bool) $this->_formStand->is_active_chat->getValue();
        $this->_exhibStand->setExhibitorInfo($this->_formStand->exhibitor_info->getValue());
        $this->_exhibStand->setShortContact($this->_formStand->short_contact->getValue());
        $this->_exhibStand->setShortInfo($this->_formStand->short_info->getValue());
        $this->_exhibStand->setStandKeywords($this->_formStand->stand_keywords->getValue());
    }

    private function checkExhibStandAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_exhibStand, 'ExhibStand NOT Exists (' . $this->_getParam('stand_hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->_exhibStand),
            [$this->getSelectedBaseUser()->getId(), $this->_exhibStand->getId()]
        );

        $this->forward403Unless(
            $this->userAuth->hasAccess(null, $this->_exhibStand)
        );
    }
}
