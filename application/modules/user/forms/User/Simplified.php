<?php

class User_Form_User_Simplified extends Engine_Form
{
    protected $_belong_to = 'userFormUser';

    protected $_tlabel = 'form_user_form_';

    protected $_standLevelNames = [];

    protected $_hallMapsNumbers = [];
    protected $_standNumberOptions = [];
    protected $_hallMapsUrls = [];

    protected $_standMainViewImage;
    protected $_standRegioViewImage;
    protected $_standStandardViewImage;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var ExhibStand
     */
    protected $_exhibStand;

    /**
     * @var ExhibParticipation
     */
    protected $_exhibParticipation;

    /**
     * @var ExhibStandParticipation
     */
    protected $_exhibStandParticipation;

    /**
     * @param User  $user
     * @param array $options
     */
    public function init()
    {
        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);

        $nameField = $this->createElement('text', 'name', [
            'required' => true,
            'value' => $this->_exhibStand->getName(),
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
                [$vStringLength, true],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup([$nameField], 'header');
        $this->addMainGroup();
        $this->addPassword();
        $this->addMainAdvGroup();
        $this->addStandViewStandardGroup();
    }

    public function addMainGroup()
    {
        $mainFields = null;
        $vAlreadyTakenParams = [];
        if (!$this->user->isNew()) {
            $vAlreadyTakenParams[] = ['id_user', '!=', $this->user->getId()];
        }

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('User', 'email', $vAlreadyTakenParams);

        $mainFields['first_name'] = $this->createElement('text', 'first_name', [
            'label' => $this->_tlabel . 'first-name',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->user->getFirstName(),
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $mainFields['last_name'] = $this->createElement('text', 'last_name', [
            'label' => $this->_tlabel . 'last-name',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->user->getLastName(),
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $mainFields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->user->getEmail(),
            'validators' => [
                ['NotEmpty', true],
                ['EmailAddress', true],
                [$vAlreadyTaken, true], ],
        ]);

        $this->addDisplayGroup(
            $mainFields,
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_user_info',
            ]
        );
    }

    public function addPassword()
    {
        $passwordFields = null;
        $vStringLength = new Zend_Validate_StringLength(['min' => 6, 'max' => 20]);
        $vIdentival = new Zend_Validate_Identical('password');

        $passwordFields['password'] = $this->createElement('password', 'password', [
            'label' => $this->_tlabel . 'password',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                [$vStringLength, true],
                ['NotEmpty', true], ],
        ]);

        $passwordFields['password_repeat'] = $this->createElement('password', 'password_repeat', [
            'label' => $this->_tlabel . 'repeat-password',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                [$vStringLength, true],
                [$vIdentival, true],
                ['NotEmpty', true], ],
        ]);

        if (!$this->user->isNew()) {
            $passwordFields['password']->setRequired(false);
            $passwordFields['password']->setAllowEmpty(true);
            $passwordFields['password_repeat']->setRequired(false);
            $passwordFields['password_repeat']->setAllowEmpty(true);
            $passwordFields['password']->setDescription('Insert new password to change');
        }

        $this->addDisplayGroup(
            $passwordFields,
            'main-pass',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_password',
            ]
        );
    }

    public function addMainAdvGroup()
    {
        $fields = [];
        $view = Zend_Registry::get('Zend_View');

        $standLevelListOptions = [];
        $standLevelList = Doctrine_Query::create()
            ->from('StandLevel sl')
            ->where('sl.id_event IS NULL OR sl.id_event = ?', $this->_exhibStand->Event->getId())
            ->execute()
            ->toKeyValueArray('id_stand_level', 'name')
        ;

        foreach ($standLevelList as $id_stand_level => $name) {
            $standLevelListOptions[$id_stand_level] = $this->getView()->translate('form_event-admin-stand_stand-level_' . $name);
        }

        // typ stoiska
        $fields['id_stand_level'] = $this->createElement('select', 'id_stand_level', [
            'label' => $this->_tlabel . 'stand-level',
            'multiOptions' => $standLevelListOptions,
            'allowEmpty' => false,
            'value' => (int) $this->_exhibStand->id_stand_level,
            'validators' => [
                ['InArray', true, [array_keys($standLevelListOptions)]],
            ],
        ]);

        $eventHallMapsList = Doctrine_Query::create()
            ->select('ehm.id_event_hall_map, t.name as name, ehm.uri, esn.name, esn.id_stand_level, es.id_exhib_stand, sl.name')
            ->from('EventHallMap ehm INDEXBY ehm.id_event_hall_map')
            ->leftJoin('ehm.Translations t WITH t.id_language = ? ', Engine_I18n::getLangId())
            ->innerJoin('ehm.EventStandNumbers esn INDEXBY esn.id_event_stand_number')
            ->innerJoin('esn.StandLevel sl INDEXBY sl.id_stand_level')
            ->leftJoin('esn.ExhibStand es INDEXBY esn.id_exhib_stand')
            ->where('ehm.id_event = ?', $this->_exhibStand->Event->getId())
            ->addWhere('esn.is_active = 1')
            ->execute([], Doctrine::HYDRATE_ARRAY)
        ;

        $eventHallMapsOptions = [];

        $eventHallMapsOptions = [];
        foreach ($eventHallMapsList as $k => $v) {
            $eventHallMapsOptions[$k] = $v['name'];
            $hallMapsUris[$k] = $v['uri'];

            foreach ($v['EventStandNumbers'] as $kk => $vv) {
                if (!isset($standLevelNames[$vv['id_stand_level']])) {
                    $this->_standLevelNames[$vv['id_stand_level']] = $this->getView()->translate('form_event-admin-stand_stand-level_' . $vv['StandLevel']['name']);
                }

                if (empty($vv['ExhibStand']) ||
                    $kk === $this->_exhibStand->id_event_stand_number ||
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

        // nr stoiska
        $standNumerOptions = $this->_hallMapsNumbers[$id_event_hall_map][$this->_exhibStand->id_stand_level];
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

        // wojewodztwo
        $fields['stand_has_address_province'] = $this->createElement('multiFieldSelect', 'stand_has_address_province', [
            'label' => $this->_tlabel . 'province',
            'multiOptions' => $provinceList,
            'allowEmpty' => false,
            'required' => false,
            'value' => $standHasAddressProvince,
            'label_add-item' => $this->_tlabel . 'add-province',
        ]);

        // ID do chat'u
        $fields['live_chat_group_id'] = $this->createElement('text', 'live_chat_group_id', [
            'label' => $this->_tlabel . 'live-chat-group',
            'allowEmpty' => true,
            'required' => false,
            'value' => $this->_exhibStand->live_chat_group_id,
            'filters' => ['StringTrim'],
            'validators' => [
                ['Int', true],
            ],
        ]);

        $fields['live_chat_group_id']->setDescription('cyfry 0-9');

        $this->addDisplayGroup(
            $fields,
            'main-adv',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_main-adv',
            ]
        );
    }

    public function addStandViewStandardGroup()
    {
        $fileDescriptionSize = $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB';

        $fields = [];
        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        //logo stoiska
        $fields['image_logo'] = $this->createElement('FileImage', 'image_logo', [
            'label' => 'form_event_stand_logo',
            'allowEmpty' => false,
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        $fields['image_logo']->getDecorator('row')->setOption('class', 'form-item stand_view_standard');

        if (!empty($this->_exhibStand->id_image_logo)) {
            $imageDecorator = $fields['image_logo']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_exhibStand->id_image_logo),
            ]);
        } else {
            $fields['image_logo']->setRequired(true);
        }

        $this->addDisplayGroup(
            $fields,
            'stand-view-standard',
            [
                'class' => 'group-wrapper group-main group_stand-view-standard',
                'legend' => $this->_tlabel . 'group_stand-view-standard',
            ]
        );
    }

    public function isValid($data)
    {
        $_data = $data[$this->getElementsBelongTo()];

        $id_stand_level = isset($_data['id_stand_level']) ? $_data['id_stand_level'] : $this->_exhibStand->id_stand_level;
        $id_event_hall_map = isset($_data['event_hall_map']) ? $_data['event_hall_map'] : $this->_exhibStand->Event->id_event_hall_map;
        $multiOptions = (array) @$this->_hallMapsNumbers[$id_event_hall_map][$id_stand_level];
        $standNumberField = $this->getElement('id_event_stand_number');
        $standNumberField->setMultiOptions($multiOptions);
        $inArray = $standNumberField->getValidator('InArray');
        $inArray->setHaystack(array_keys($multiOptions));

        return parent::isValid($data);
    }

    public function postIsValid($data)
    {
        $_data = null;
        $id_stand_level = null;
        $data = $data[$this->_belong_to];

        //populate main
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];

        //populate password
        $pass = $this->password->getValue();
        if (!empty($pass)) {
            $this->user->password = $this->user->getHashPassword($data['password']);
        }

        // zapis stand'a
        $this->_exhibStand->setName($data['name']);
        $id_event_hall_map = isset($_data['event_hall_map']) ? $_data['event_hall_map'] : $this->_exhibStand->Event->id_event_hall_map;
        $multiOptions = (array) @$this->_hallMapsNumbers[$id_event_hall_map][$id_stand_level];

        $standNumberField = $this->getElement('id_event_stand_number');
        $standNumberField->setMultiOptions($multiOptions);
        $inArray = $standNumberField->getValidator('InArray');
        $inArray->setHaystack(array_keys($multiOptions));

        //generujemy uri
        $this->_exhibStand->setUri(Engine_Utils::_()->getFriendlyUri($this->_exhibStand->getName()));

        $this->_exhibStand->id_stand_level = $data['id_stand_level'];
        // $this->_exhibStand->StandViewImage = 9;
        $this->_exhibStand->id_event_stand_number = $data['id_event_stand_number'];
        $this->_exhibStand->live_chat_group_id = $data['live_chat_group_id'];

        // zapisanie wojewodztwa
        $this->_exhibStand->StandHasAddressProvince->clear();
        $selected_address_province = array_flip((array) $data['stand_has_address_province']);
        foreach (array_keys($selected_address_province) as $k) {
            if (!empty($k)) {
                $standHasAddressProvince = new StandHasAddressProvince();
                $standHasAddressProvince->id_address_province = $k;
                $this->_exhibStand->StandHasAddressProvince->add($standHasAddressProvince);
            }
        }

        // zapisanie logo
        if (isset($_FILES['image_logo']) && 0 === $_FILES['image_logo']['error']) {
            $image_logo = Service_Image::createImage(
                $this->_exhibStand,
                [
                    'type' => $_FILES['image_logo']['type'],
                    'name' => $_FILES['image_logo']['name'],
                    'source' => $_FILES['image_logo']['tmp_name'], ]
            );

            $this->_exhibStand->ImageLogo = $image_logo;
        }

        // zapisanie hostessy (losowanie)
        $hostessListOption = Doctrine_Query::create()
            ->select('sh.id_exhib_stand_hostess, CONCAT(id_exhib_stand_hostess, \'.\', sh.file_ext ) as file_name')
            ->from('ExhibStandHostess sh')
            ->where('sh.is_active = 1 AND sh.id_base_user = ?', [$this->_exhibStand->id_base_user])
            ->orderBy('is_animated')
            ->execute()
            ->toKeyValueArray('id_exhib_stand_hostess', 'file_name')
        ;

        $this->_exhibStand->id_exhib_stand_hostess = array_rand($hostessListOption);

        //zapis typu stoiska
        $this->_exhibStand->id_stand_level = $data['id_stand_level'];

        // wybranie tla stoiska w zaleznosci od jego typu
        $exhibStandViewImage = Doctrine_Query::create()
            ->from('ExhibStandViewImage esvi')
            ->leftJoin('esvi.EventHasStandViewImage ehevi')// $this->_exhibStand->id_event)
            ->addWhere('esvi.id_stand_level = ?', $this->_exhibStand->id_stand_level)
            ->addWhere('(esvi.is_public = 1 OR ehevi.id_event = ?)', $this->_exhibStand->id_event)
            ->orderBy('rand()')
            ->fetchOne()
        ;

        $this->_exhibStand->ExhibStandViewImage = $exhibStandViewImage;

        // dodanie user'a do aktualnie wybranego event'u
        $exhibParticipationType = ExhibParticipationType::find(ExhibParticipationType::TYPE_EXHIBITOR); // wystawca
        $this->_exhibParticipation->User = $this->user; // dodany przd chwila do bazy
        $this->_exhibParticipation->ExhibParticipationType = $exhibParticipationType;
        $this->_exhibParticipation->is_active = true;
        $this->_exhibParticipation->UserCreated = $this->userAuth;

        // dodanie stoiska do event'u
        $this->_exhibStandParticipation->UserCreated = $this->userAuth;
        $this->_exhibStandParticipation->ExhibStand = $this->_exhibStand;
        $this->_exhibStandParticipation->ExhibParticipation = $this->_exhibParticipation; // dodane przed chwila do bazy

        return true;
    }

    /**
     * @param $user User
     */
    protected function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $exhibitStand ExhibStand
     */
    protected function setExhibitStand($exhibitStand)
    {
        $this->_exhibStand = $exhibitStand;
    }

    /**
     * @param $exhibParticipation ExhibParticipation
     */
    protected function setExhibParticipation($exhibParticipation)
    {
        $this->_exhibParticipation = $exhibParticipation;
    }

    /**
     * @param $exhibStandParticipation ExhibStandParticipation
     */
    protected function setExhibStandParticipation($exhibStandParticipation)
    {
        $this->_exhibStandParticipation = $exhibStandParticipation;
    }
}
