<?php

class Event_Form_Admin_Stand extends Engine_Form
{
    protected $_belong_to = 'EventFormAdminStand';

    /**
     * @var ExhibStand
     */
    protected $_exhibStand;

    protected $_tlabel = 'form_event-admin-stand_';

    protected $_standLevelNames = [];

    protected $_hallMapsNumbers = [];

    protected $_hallMapsUrls = [];

    protected $_userAuth;

//    protected $_event;

    protected $_language;

    protected $_eventHallMapsList;

    /**
     * @param ExhibStand $exhib_stand
     * @param array      $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);

        $nameField = $this->createElement('text', 'name', [
            'required' => true,
            'description' => $this->_tlabel . 'name-description',
            'value' => $this->_exhibStand->getName(),
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
                [$vStringLength, true],
            ],
        ]);

        $eventHallMapsList = $this->getEventHalls();

        $buttons = [];
        $buttons['submit'] = $this->createElement('submit', 'submit', [
            'label' => 'cms-button_save',
        ]);

        $stand_uri = $this->_exhibStand->getUri();
        if (!empty($stand_uri)) {
            $buttons['preview'] = $this->createElement('dummy', 'preview_stand', [
                'type' => 'button',
                'ignore' => true,
            ]);

            $buttons['preview']->clearDecorators();
            $class = 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button-nicy ui-button-text dummy_stand_prev';
            $helperUrl = new Zend_View_Helper_Url();
            $buttons['preview']->setContent('<div class="ui-button-big" style="top: 60px;"><div class="button"><a class="contentLoad  ' . $class . '" href="' . $helperUrl->url(['stand_uri' => $this->_exhibStand->getUri(), 'hall_uri' => $this->_exhibStand->EventStandNumber->EventHallMap->uri, 'event_uri' => $this->_exhibStand->Event->getUri()], 'event_admin-stand-preview-stand-iframe') . '" maxHeightLightbox="590px" widthLightbox="1035px">' . $this->translate('label_form_stand_preview_stand') . '</a></div></div>');

            //button klonowania
            if (count($eventHallMapsList) > 1) {
                $buttons['clone'] = $this->createElement('dummy', 'clone_stand', [
                    'type' => 'button',
                    'ignore' => true,
                ]);
                $buttons['clone']->clearDecorators();
                $class = 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button-nicy ui-button-text dummy_stand_prev';
                $helperUrl = new Zend_View_Helper_Url();
                $buttons['clone']->setContent('<div class="ui-button-big" style="margin-top:15px;"><div class="button"><a class="contentLoad  ' . $class . '" href="' . $helperUrl->url(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand-clone') . '" maxHeightLightbox="300px" widthLightbox="600px">' . $this->translate('Clone stand') . '</a></div></div>');
            }
        }

        $this->addDisplayGroup([$nameField], 'header');
        $this->addDisplayGroup($buttons, 'buttons');
        $this->addMainAdvGroup();
        $this->addAsideGroup();
        $this->addStandViewStandardGroup();
        $this->addBasicGroup();
        $this->addSeoGroup();
    }

    public function addSeoGroup()
    {
        $metatag_title = $this->createElement('text', 'metatag_title', [
            'label' => $this->_tlabel . 'seo-title',
            'allowEmpty' => false,
            'value' => $this->_exhibStand->getMetatagTitle(),
            'filters' => ['StringTrim'],
        ]);

        $metatag_key = $this->createElement('text', 'metatag_key', [
            'label' => $this->_tlabel . 'seo-keywords',
            'allowEmpty' => false,
            'value' => $this->_exhibStand->getMetatagKey(),
        ]);

        $metatag_desc = $this->createElement('text', 'metatag_desc', [
            'label' => $this->_tlabel . 'seo-desc',
            'allowEmpty' => false,
            'value' => $this->_exhibStand->getMetatagDesc(),
            'filters' => ['StringTrim'],
        ]);
        $is_metatag = $this->createElement('checkbox', 'is_metatag', [
            'label' => $this->_tlabel . 'seo-active',
            'uncheckedValue' => '0',
            'class' => 'seoActive',
            'onclick' => 'seoActive()',
            'value' => $this->_exhibStand->getIsMetatag(),
        ]);

        $metatag_title->getDecorator('row')->setOption('class', 'form-item metatag_title_content');
        $metatag_key->getDecorator('row')->setOption('class', 'form-item metatag_key_content');
        $metatag_desc->getDecorator('row')->setOption('class', 'form-item metatag_desc_content');

        $this->addDisplayGroup(
            [$is_metatag, $metatag_title, $metatag_key, $metatag_desc],
            'seo',
            [
                'legend' => $this->_tlabel . 'group_seo',
                'class' => 'group-wrapper group-main',
            ]
        );
    }

    public function addMainAdvGroup()
    {
        $fields = [];
        $view = Zend_Registry::get('Zend_View');

        /** @var $eventList */
        // wyciagamy tylko jeden ponieważ wszystko co się dzieje dalej jest on niego zależne
        // robimy zapytanie io przepisujemy do tablicy, jeśli kiedyś byłaby potrzeba
        // przewrócenia pełnej listy
        $eventList = Doctrine_Query::create()
            ->from('Event e')
            ->where('e.id_event = ?', $this->_exhibStand->Event->getId())
            ->execute()
        ;

        $eventOptions = [];
        foreach ($eventList as $event) {
            $eventOptions[$event->getId()] = $event->getTitle();
        }

        $fields['id_event'] = $this->createElement(
            'select',
            'id_event',
            [
                'label' => $this->_tlabel . 'event-name',
                'multiOptions' => $eventOptions,
                'allowEmpty' => false,
                'value' => (int) $this->_exhibStand->Event->getId(),
                'validators' => [
                    ['InArray', true, [array_keys($eventOptions)]],
                ],
            ]
        );

        $eventHallMapsList = $this->getEventHalls();

        $eventHallMapsOptions = [];
        foreach ($eventHallMapsList as $k => $v) {
            $eventHallMapsOptions[$k] = $v['name'];
            $hallMapsUris[$k] = $v['uri'];

            foreach ($v['EventStandNumbers'] as $kk => $vv) {
                if (!isset($standLevelNames[$vv['id_stand_level']])) {
                    $this->_standLevelNames[$vv['id_stand_level']] = $this->getView()->translate('form_event-admin-stand_stand-level_' . $vv['StandLevel']['name']);
                }

                if (empty($vv['ExhibStand']) ||
                    $kk == $this->_exhibStand->id_event_stand_number ||
                    ExhibStand::TYPE_TEST === $this->_exhibStand->id_stand_type
                ) {
                    $this->_hallMapsNumbers[$k][$vv['id_stand_level']][$kk] = $vv['name'];
                }
            }
        }

        foreach ($hallMapsUris as $k => $uri) {
            $this->_hallMapsUrls[$k] = $view->url(['event_hash' => $this->_exhibStand->Event->getHash(), 'hall_uri' => $uri], 'event_admin-hall-stands-preview-iframe');
        }

        $id_event_hall_map = $this->_exhibStand->isNew() ? $this->_exhibStand->Event->id_event_hall_map : $this->_exhibStand->EventStandNumber->id_event_hall_map;
        $fields['event_hall_map'] = $this->createElement('select', 'event_hall_map', [
            'label' => $this->_tlabel . 'event_hall_map',
            'multiOptions' => $eventHallMapsOptions,
            'allowEmpty' => false,
            'value' => $id_event_hall_map,
            'validators' => [
                ['InArray', true, [array_keys($eventHallMapsOptions)]],
            ],
            'data-hall-map-numbers' => json_encode($this->_hallMapsNumbers),
            'data-urls' => json_encode($this->_hallMapsUrls),
        ]);

        $fields['id_stand_level'] = $this->createElement('select', 'id_stand_level', [
            'label' => $this->_tlabel . 'stand-level',
            'multiOptions' => $this->_standLevelNames,
            'allowEmpty' => false,
            'value' => $this->_exhibStand->id_stand_level,
            'validators' => [
                ['InArray', true, [array_keys($this->_standLevelNames)],
                ], ],
        ]);

        $standNumerOptions = $this->_hallMapsNumbers[$id_event_hall_map][$this->_exhibStand->id_stand_level] ?? [];

        $fields['id_event_stand_number'] = $this->createElement('select', 'id_event_stand_number', [
            'label' => $this->_tlabel . 'box-number',
            'multiOptions' => $standNumerOptions,
            'allowEmpty' => false,
            'required' => true,
            'value' => $this->_exhibStand->id_event_stand_number,
            'validators' => [
                ['InArray', true, [array_keys($standNumerOptions)]],
            ],
        ]);

        $fields['id_event_stand_number']->clearDecorators();
        $fields['id_event_stand_number']
            ->addDecorator('ViewHelper')
            ->addDecorator('Description', ['tag' => 'p', 'class' => 'field-description'])
            ->addDecorator('Errors')
            ->addDecorator('HallStandPreview', ['link' => $view->url(['event_hash' => $this->_exhibStand->Event->getHash(), 'hall_uri' => 'main_hall'], 'event_admin-hall-stands-preview-iframe')])
            ->addDecorator(['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'field-wrapper'])
            ->addDecorator('Label')
            ->addDecorator(['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'form-item'])
        ;

        $provinceList = Doctrine_Core::getTable('AddressProvince')->createQuery('ap')
            ->select('ap.*')
            ->execute()
            ->toKeyValueArray('id_address_province', 'name')
        ;

        $standHasAddressProvince = $this->_exhibStand->StandHasAddressProvince->toKeyValueArray('id_address_province', 'id_address_province');

        $fields['stand_has_address_province'] = $this->createElement('multiFieldSelect', 'stand_has_address_province', [
            'label' => $this->_tlabel . 'province',
            'multiOptions' => $provinceList,
            'allowEmpty' => false,
            'required' => false,
            'value' => $standHasAddressProvince,
            'label_add-item' => $this->_tlabel . 'add-province',
        ]);

        $fields['live_chat_group_id'] = $this->createElement('text', 'live_chat_group_id', [
            'label' => $this->_tlabel . 'live-chat-group',
            // 'description' => $this->_tlabel.'chat-group_description',
            'allowEmpty' => true,
            'required' => false,
            'value' => $this->_exhibStand->live_chat_group_id,
            'filters' => ['StringTrim'],
            'validators' => [
                ['Int', true],
            ],
        ]);

        // SQL REQUIED: ALTER TABLE exhib_stand ADD COLUMN skype_name varchar(255)
        $fields['skype_name'] = $this->createElement('text', 'skype_name', [
            'label' => $this->_tlabel . 'skype-name',
            // 'description' => $this->_tlabel.'chat-group_description',
            'allowEmpty' => true,
            'required' => false,
            'value' => $this->_exhibStand->skype_name,
        ]);

//        $fields['live_chat_group_id']->setDescription('cyfry 0-9');

        if (DEBUG) {
            $fields['css_class'] = $this->createElement('text', 'css_class', [
                'label' => $this->_tlabel . 'css_class',
                'allowEmpty' => true,
                'required' => false,
                'value' => $this->_exhibStand->css_class,
                'filters' => ['StringTrim'],
            ]);
        }

        $concat_participation_name = !empty($this->_exhibStand->id_event) ? "CONCAT(u.last_name, ' ', u.first_name), u.company, u.email" : "CONCAT(u.last_name, ' ', u.first_name), u.company, u.email, e.title";
        $participationQuery = Doctrine_Query::create()
            ->select("p.hash, CONCAT_WS(', ',{$concat_participation_name}) as participation_name")
            ->from('ExhibParticipation p')
            ->leftJoin('p.User u')
            ->leftJoin('p.Event e')
            ->addWhere('p.is_active = 1 AND p.id_exhib_participation_type IN (?) AND p.id_base_user = ?', [ExhibParticipationType::TYPE_EXHIBITOR, $this->_exhibStand->id_base_user])
            ->addWhere('p.id_event = ? ', [$this->_exhibStand->id_event])
        ;

        $participationListOptions = $participationQuery->execute()
            ->toKeyValueArray('hash', 'participation_name')
        ;

        $ExhibStandParticipationsHashes = [];
        if (!empty($this->_exhibStand)) {
            $ExhibStand = ExhibStand::findOneByHash($this->_exhibStand->getHash(), $this->baseUser);
            if (!empty($ExhibStand->ExhibStandParticipation)) {
                $ExhibStandParticipations = $ExhibStand->ExhibStandParticipation;
                foreach ($ExhibStandParticipations as $ExhibStandParticipation) {
                    if ($ExhibStandParticipation->is_active) {
                        $ExhibStandParticipationsHashes[] = $ExhibStandParticipation->ExhibParticipation->getHash();
                    }
                }
            }
        }

        $fields['participation'] = $this->createElement('multiselect', 'participation', [
            'label' => $this->_tlabel . 'participation',
            'multiOptions' => $participationListOptions,
            'allowEmpty' => false,
            'required' => true,
            'value' => $ExhibStandParticipationsHashes,
            'class' => 'multiselect',
            'style' => 'width:610px; height: 200px;',
            'validators' => [
                ['InArray',
                    true,
                    [array_keys($participationListOptions)], ],
            ],
        ]);

        $vOnlyOneEvent = new Private_Validate_OnlyOneEvent();
        //Dodanie walidatora do pola title w fomularzu
        $fields['participation']->addValidator($vOnlyOneEvent);

        $decorator = $fields['participation']->getDecorator('data');
        $decorator->setOption('style', 'width: 601px;margin-top:10px;');

        $this->addDisplayGroup(
            $fields,
            'main-adv',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_main-adv',
            ]
        );
    }

    public function addAsideGroup()
    {
        $fields = [];

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];
        $fields['is_active'] = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_exhibStand->is_active,
            'validators' => [
                ['InArray', true, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $fields['is_active_chat'] = $this->createElement('select', 'is_active_chat', [
            'label' => $this->_tlabel . 'is-active-chat',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_exhibStand->is_active_chat,
            'validators' => [
                ['InArray',
                    true,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $fields['is_contact_active'] = $this->createElement('select', 'is_contact_active', [
            'label' => $this->_tlabel . 'is_contact_active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_exhibStand->is_contact_active,
            'validators' => [
                ['InArray',
                    true,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $this->addDisplayGroup($fields, 'aside', ['class' => 'group-wrapper group-aside exClearRight']);
    }

    public function addBasicGroup()
    {
        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);
        $fields = [];

        $fields['fb_address'] = $this->createElement('text', 'fb_address', [
            'label' => $this->_tlabel . 'fb-address',
            'required' => false,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->_exhibStand->getFbAddress(),
        ]);

        //Image FACEBOOK
        $fields['image_fb'] = $this->createElement('FileImage', 'image_fb', [
            'label' => 'form_event_stand_image_fb',
            'required' => false,
            'allowEmpty' => true,
            'item-class' => 'stand_view_standard',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            // 'description' => $this->_tlabel.'image-logo-desc',
        ]);
        if (!empty($this->_exhibStand->id_image_fb)) {
            $imageDecorator = $fields['image_fb']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_exhibStand->id_image_fb),
            ]);
        }

        $fields['www_adress'] = $this->createElement('text', 'www_adress', [
            'label' => $this->_tlabel . 'www_adress',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_exhibStand->getWwwAdress(),
        ]);

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        $fields['short_contact'] = $this->createElement('textarea', 'short_contact', [
            'label' => $this->_tlabel . 'short_contact',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_exhibStand->getShortContact(),
        ]);

        $fields['short_info'] = $this->createElement('textarea', 'short_info', [
            'label' => $this->_tlabel . 'short_info',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_exhibStand->getShortInfo(),
        ]);

        $fields['stand_keywords'] = $this->createElement('textarea', 'stand_keywords', [
            'label' => $this->_tlabel . 'keywords',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_exhibStand->getStandKeywords(),
            'validators' => [[$vStringLength, true]],
        ]);

        if (Engine_Variable::getInstance()->getVariable(Variable::ADDITIONAL_CONTACT_FIELD_ON)) {
            $fields['contact_info'] = $this->createElement('wysiwyg', 'contact_info', [
                'label' => $this->_tlabel . 'contact-info',
                'editor' => 'full',
                'wrapper-class' => 'full',
                'value' => $this->_exhibStand->getContactInfo(),
            ]);
        }

        //zdjęcie do podstrony o nas
        $fields['image_about_us'] = $this->createElement('FileImage', 'image_about_us', [
            'label' => 'form_event_stand_exhibitor_image',
            'required' => false,
            'allowEmpty' => true,
            'item-class' => 'stand_view_standard',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            // 'description' => $this->_tlabel.'image-logo-desc',
        ]);
        if (!empty($this->_exhibStand->id_image_about_us)) {
            $imageDecorator = $fields['image_about_us']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_exhibStand->id_image_about_us),
            ]);
        }

        $fields['exhibitor_info'] = $this->createElement('wysiwyg', 'exhibitor_info', [
            'label' => $this->_tlabel . 'exhibitor-info',
            'editor' => 'full',
            'wrapper-class' => 'full',
            'value' => $this->_exhibStand->getExhibitorInfo(),
        ]);

        $fields['google_analytics'] = $this->createElement('textarea', 'google_analytics', [
            'label' => $this->_tlabel . 'google_analytics',
            'required' => false,
            'value' => $this->_exhibStand->getGoogleAnalytics(),
            'filters' => ['StringTrim', 'StripTags'],
        ]);

        $this->addDisplayGroup($fields, 'text', [
            'class' => 'group-wrapper group-main',
            'legend' => $this->_tlabel . 'group_basic',
        ]);
    }

    public function addStandViewStandardGroup()
    {
        $fields = [];
        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];
//        $fileDescriptionSize = '. Maksymalna wielkość pliku '.number_format(MAX_FILE_SIZE/(1024*1024), 2).' Mb';

        //LOGO STOISKA
        $fields['image_logo'] = $this->createElement('FileImage', 'image_logo', [
            'label' => 'form_event_stand_logo',
            'allowEmpty' => false,
            'item-class' => 'stand_view_standard',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            // 'description' => $this->_tlabel.'image-logo-desc',
        ]);

        if (!empty($this->_exhibStand->id_image_logo)) {
            $imageDecorator = $fields['image_logo']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_exhibStand->id_image_logo),
            ]);
        } else {
            $fields['image_logo']->setRequired(true);
        }

        // POBRANIE OBRAZKÓW DLA STOISKA
        $eventViewImageArray = ['0'];

        $eventViewImageList = Doctrine_Query::create()
            ->from('EventHasStandViewImage ehsvi')
            ->where('ehsvi.id_event = ?', $this->_exhibStand->id_event)
            ->execute()
        ;

        foreach ($eventViewImageList as $image) {
            $eventViewImageArray[] = $image->id_exhib_stand_view_image;
        }

        $standViewImageQuery = Doctrine_Query::create()
            ->from('ExhibStandViewImage svi')
            ->where('svi.is_active = 1 AND svi.id_exhib_stand IS NULL')
            ->addWhere('svi.is_public = 1 OR svi.id_exhib_stand_view_image IN ?', [$eventViewImageArray])
        ;

        if (!$this->_exhibStand->isNew()) {
            $standViewImageQuery->orWhere('svi.id_exhib_stand = ?', $this->_exhibStand->getId());
        }

        $standViewImageList = $standViewImageQuery->execute();

        $standViewOptions = [];
        /** @var ExhibStandViewImage $standViewImage */
        foreach ($standViewImageList as $standViewImage) {
            $standViewOptions[$standViewImage->id_stand_level][$standViewImage->getId()] = [
                'src' => Service_Image::getUrl($standViewImage->id_image, ['width' => 144]),
            ];
        }

        // tła stoiska
        foreach (array_keys($standViewOptions) as $id_stand_level) {
            $fields['stand_view_image_' . $id_stand_level] = $this->createElement('radioImage', 'stand_view_image_' . $id_stand_level, [
                'label' => $this->_tlabel . 'stand-view',
                'wrapper-class' => 'full',
                'multiOptions' => $standViewOptions[$id_stand_level],
                'item-class' => 'form-item_stand-block form-item_stand-block_' . $id_stand_level,
                'value' => $this->_exhibStand->id_exhib_stand_view_image,
                'validators' => [
                    ['InArray', true, [array_keys($standViewOptions[$id_stand_level])]],
                ],
            ]);
        }

        $hostessListOptions = ['' => [
            'label_attribs' => ['class' => 'image_empty'],
            'src' => $this->_exhibStand->BaseUser->getPublicBrowserPath() . '/_images/empty_radio_image.png',
        ]];

        $exhibStandHostessQuery = Doctrine_Query::create()
            ->from('ExhibStandHostess sh')
            ->where('sh.is_active = 1 AND sh.id_exhib_stand IS NULL')
            ->orderBy('sh.is_animated ASC')
        ;

        if (!$this->_exhibStand->isNew()) {
            $exhibStandHostessQuery->orWhere('sh.id_exhib_stand = ?', $this->_exhibStand->getId());
        }

        $ExhibStandHostessList = $exhibStandHostessQuery->execute();

        /** @var ExhibStandHostess $exhibStandHostess */
        foreach ($ExhibStandHostessList as $exhibStandHostess) {
            $optionAttribs = [];
            $labelContent = '';
            if ($exhibStandHostess->is_animated) {
                $optionAttribs = ['class' => 'is_animated'];
                $labelContent = '<span>' . $this->_view->translate($this->_tlabel . 'hostess_is-animated') . '</span>';
            }

            $hostessListOptions[$exhibStandHostess->getId()] = [
                'label_attribs' => $optionAttribs,
                'label_content' => $labelContent,
                'src' => $exhibStandHostess->getBrowserThumbHostess(),
            ];
        }

        $fields['hostess'] = $this->createElement('RadioImage', 'hostess', [
            'label' => $this->_tlabel . 'hostess',
            'value' => null === $this->_exhibStand->id_exhib_stand_hostess ? 0 : $this->_exhibStand->id_exhib_stand_hostess,
            'allowEmpty' => true,
            'wrapper-class' => 'full',
            'validators' => [
                ['InArray',
                    true,
                    [array_keys($hostessListOptions)], ],
            ],
        ]);

        $fields['hostess']->setMultioptions($hostessListOptions);
        $fields['hostess']->getDecorator('row')->setOption('class', 'form-item stand_view_standard');

        $this->addDisplayGroup(
            $fields,
            'stand-view-standard',
            [
                'class' => 'group-wrapper group-main group_stand-view-standard',
                'legend' => $this->_tlabel . 'group_stand-view-standard',
            ]
        );
    }

    public function addBrandGroup()
    {
        $exhibStandHasBrand = $this->_exhibStand->ExhibStandHasBrand->toKeyValueArray('id_brand', 'id_brand');
        $brandQuery = Doctrine_Query::create()
            ->from('Brand b')
            ->innerJoin('b.Translations bt WITH bt.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('b.ChildBrands cb WITH cb.is_active = 1')
            ->leftJoin('cb.Translations cbt WITH cbt.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('cb.ChildBrands cb1 WITH cb1.is_active = 1')
            ->leftJoin('cb1.Translations cbt1 WITH cbt1.id_language = ?', Engine_I18n::getLangId())
            ->where('b.is_active = 1 AND b.id_brand_parent IS NULL')
            ->orderBy('bt.name ASC, cbt.name ASC, cbt1.name ASC')
        ;

        $eventBrandList = $this->_exhibStand->Event->EventHasBrand->toKeyValueArray('id_brand', 'id_brand');
        $eventBrandList = array_merge($exhibStandHasBrand, $eventBrandList);
        if (!empty($eventBrandList)) {
            $brandQuery->addWhere(
                '(b.id_brand IN ? OR cb.id_brand IN ? OR cb1.id_brand IN ? OR cb.id_brand_parent IN ?)',
                [$eventBrandList, $eventBrandList, $eventBrandList, $eventBrandList]
            );
        }

        $brandList = $brandQuery->execute();
        $multiOptions = [];

        /** @var Brand $brand */
        foreach ($brandList as $brand) {
            $multiOptions[$brand->getId()] = $brand->getName();

            /** @var Brand $childBrands */
            foreach ($brand->ChildBrands as $childBrands) {
                $multiOptions[$childBrands->getId()] = '- - - ' . $childBrands->getName();

                /** @var Brand $childBrands */
                foreach ($childBrands->ChildBrands as $childBrands2) {
                    $multiOptions[$childBrands2->getId()] = '- - - - - - ' . $childBrands2->getName();
                }
            }
        }

        $brandField = $this->createElement('multiFieldSelect', 'exhib_stand_has_brand', [
            'label' => $this->_tlabel . 'brand',
            'required' => false,
            'allowEmpty' => false,
            'value' => $exhibStandHasBrand,
            'multiOptions' => $multiOptions,
            'label_add-item' => $this->_tlabel . 'add-brand',
        ]);

        $this->addDisplayGroup(
            [$brandField],
            'brand',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_brand',
            ]
        );
    }

    public function populateModel()
    {
        $subForms = $this->getSubForms();
        foreach ($subForms as $subForm) {
            $populateMethod = 'populate' . ucfirst($subForm->getName());
            if (method_exists($this, $populateMethod)) {
                $this->{$populateMethod}();
            }
        }
    }

    public function populateBrand()
    {
        $gBrand = $this->getDisplayGroup('brand');
        if ($gBrand !== null) {
            $this->_exhibStand->ExhibStandHasBrand->clear();
            $brandField = array_flip((array) $this->exhib_stand_has_brand->getValue());

            foreach (array_keys($brandField) as $id_brand) {
                if (!empty($id_brand)) {
                    $exhibStandHasBrand = new ExhibStandHasBrand();
                    $exhibStandHasBrand->id_brand = $id_brand;
                    $this->_exhibStand->ExhibStandHasBrand->add($exhibStandHasBrand);
                }
            }
        }
    }

    public function isValid($data)
    {
        $_data = $data[$this->getElementsBelongTo()];
        $id_stand_level = isset($_data['id_stand_level']) ? $_data['id_stand_level'] : $this->_exhibStand->id_stand_level;
        $id_event_hall_map = isset($_data['event_hall_map']) ? $_data['event_hall_map'] : $this->_exhibStand->Event->id_event_hall_map;
        $multiOptions = (array) @$this->_hallMapsNumbers[$id_event_hall_map][$id_stand_level];

        // czy grupa main-adv istnieje
        if (null !== $this->getDisplayGroup('main-adv')) {
            $standNumberField = $this->getElement('id_event_stand_number');
            $standNumberField->setMultiOptions($multiOptions);
            $inArray = $standNumberField->getValidator('InArray');
            $inArray->setHaystack(array_keys($multiOptions));
        }

        $standViewFiled = $this->getElement('stand_view_image_' . $id_stand_level);
        $standViewFiled->setRequired(true)->setAllowEmpty(false);

        return parent::isValid($data);
    }

    public function render(Zend_View_Interface $view = null)
    {
        foreach (array_keys($this->_standLevelNames) as $id_stand_level) {
            $stand_view_image_element = $this->getElement('stand_view_image_' . $id_stand_level);
            if ($stand_view_image_element !== null) {
                $stand_view_image_element->getDecorator('row')->setOption('style', 'display: none');
            }
        }

        $stand_level = $this->getElement('id_stand_level');
        $id_stand_level_value = $stand_level !== null ? $stand_level->getValue() : $this->_exhibStand->id_stand_level;
        if ($id_stand_level_value) {
            $stand_view_image_element = $this->getElement('stand_view_image_' . $id_stand_level_value);
            if ($stand_view_image_element !== null) {
                $stand_view_image_element->getDecorator('row')->setOption('style', 'display: block');
            }
        }

        $is_metatag = $this->is_metatag->getValue();
        $metatag_title = $this->getElement('metatag_title');
        $metatag_key = $this->getElement('metatag_key');
        $metatag_desc = $this->getElement('metatag_desc');

        if (0 === $is_metatag) {
            $metatag_title->getDecorator('row')->setOption('style', 'display: none');
            $metatag_key->getDecorator('row')->setOption('style', 'display: none');
            $metatag_desc->getDecorator('row')->setOption('style', 'display: none');
        }

        return parent::render();
    }

    public function prepareFormToAdmin()
    {
    }

    public function prepareFormToExhibitor()
    {
        $this->removeElement('event_hall_map');
        $this->removeElement('is_active');
        $this->removeElement('id_event');
        $this->removeElement('id_stand_level');
        $this->removeElement('stand_has_address_province');
        $this->removeElement('live_chat_group_id');
        $this->removeElement('id_event_stand_number');
        $this->removeElement('participation');
        $this->removeElement('is_contact_active');
        $this->removeElement('clone_stand');
        $this->removeDisplayGroup('main-adv');
    }

    protected function postIsValid($data)
    {
        $_data = $this->getValues($this->_belong_to);
        if (null !== $this->getDisplayGroup('main-adv')) {
            $this->_exhibStand->id_stand_level = $_data['id_stand_level'];
            $this->_exhibStand->id_event_stand_number = $_data['id_event_stand_number'];
            $this->_exhibStand->live_chat_group_id = $_data['live_chat_group_id'];
            $this->_exhibStand->skype_name = $_data['skype_name'];

            $this->_exhibStand->StandHasAddressProvince->clear();
            $selected_address_province = array_flip((array) $_data['stand_has_address_province']);

            foreach (array_keys($selected_address_province) as $k) {
                if (!empty($k)) {
                    $standHasAddressProvince = new StandHasAddressProvince();
                    $standHasAddressProvince->id_address_province = $k;
                    $this->_exhibStand->StandHasAddressProvince->add($standHasAddressProvince);
                }
            }
        }

        $this->_exhibStand->fb_address = str_replace(
            ['http://', 'https://'],
            '',
            $_data['fb_address']
        );

        $this->_exhibStand->setWwwAdress($_data['www_adress']);
        $this->_exhibStand->setGoogleAnalytics($this->getValue('google_analytics'));

        $this->_exhibStand->id_exhib_stand_hostess = null;
        if (isset($_data['hostess']) && !empty($_data['hostess'])) {
            $this->_exhibStand->id_exhib_stand_hostess = $_data['hostess'];
        }

        $id_stand_view_image = $_data['stand_view_image_' . $this->_exhibStand->id_stand_level];
        if (!empty($id_stand_view_image)) {
            $this->_exhibStand->id_exhib_stand_view_image = $id_stand_view_image;
        }

        $element = $this->getElement('css_class');
        if ($element !== null) {
            $this->_exhibStand->css_class = $this->getValue('css_class');
        }

        // ZAPIS PLIKÓW GRAFICZNYCH
        $images_list = ['image_logo', 'image_fb', 'image_banner_top', 'image_banner_desk', 'image_banner_tv', 'image_about_us'];
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();
        foreach ($images_list as $v) {
            if (isset($files[$v]) && 0 === $files[$v]['error']) {
                $image = Service_Image::createImage($this->_exhibStand, [
                    'type' => Engine_Utils::_()->getFileExt($upload->getFileName($v, false)),
                    'name' => $upload->getFileName($v, false),
                    'source' => $upload->getFileName($v),
                ]);
                $image->save();
                $this->_exhibStand->{'id_' . $v} = $image->getId();
            }
        }

        //metatagi
        $this->_exhibStand->setIsMetatag($_data['is_metatag']);
        $this->_exhibStand->setMetatagTitle($_data['metatag_title']);
        $this->_exhibStand->setMetatagKey($_data['metatag_key']);
        $this->_exhibStand->setMetatagDesc($_data['metatag_desc']);

        return true;
    }

    private function getEventHalls()
    {
        if (!$this->_eventHallMapsList) {
            $this->_eventHallMapsList = Doctrine_Query::create()
                ->select('ehm.id_event_hall_map, t.name as name, ehm.uri, esn.name, esn.id_stand_level, es.id_exhib_stand, sl.name')
                ->from('EventHallMap ehm INDEXBY ehm.id_event_hall_map')
                ->innerJoin('ehm.EventStandNumbers esn INDEXBY esn.id_event_stand_number')
                ->innerJoin('esn.StandLevel sl INDEXBY sl.id_stand_level')
                ->leftJoin('esn.ExhibStand es INDEXBY esn.id_exhib_stand')
                ->leftJoin('ehm.Translations t WITH t.id_language = ? ', Engine_I18n::getLangId())
                ->where('ehm.id_event = ?', $this->_exhibStand->Event->getId())
                ->addWhere('esn.is_active = 1')
                ->execute([], Doctrine::HYDRATE_ARRAY)
             ;
        }

        return $this->_eventHallMapsList;
    }
}
