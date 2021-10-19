<?php

class Event_Form_Admin_StandViewImage extends Engine_Form
{
    /**
     * @var ExhibStandViewImage
     */
    protected $_model;

    protected $_belong_to = 'StandViewImage';
    protected $_tlabel = 'label_form_event-admin-stand-view-image_';

    private $_bannerData = [];

    private $_subFormBanners = [];

    /**
     * @var Doctrine_Collection
     */
    private $_standLevelList;

    public function init()
    {
        $header = [];
        $buttons = [];
        $fields = [];
        $aside = [];

        // HEADER - BEGIN
        $header['name'] = $this->createElement('text', 'name', [
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_model->getName(),
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);
        // HEADER - END

        // BUTTONS - START
        $buttons['submit'] = $this->createElement('submit', 'submit', [
            'label' => 'Save',
        ]);
        // BUTTONS - END

        // MAIN FIELDS - START
        $fields['image'] = $this->createElement('FileImage', 'image', [
            'label' => $this->_tlabel . 'image',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
        ]);

        $fields['id_exhib_stand'] = $this->createElement('text', 'id_exhib_stand', [
            'label' => $this->_tlabel . 'id_exhib_stand',
            'value' => $this->_model->id_exhib_stand,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            // 'description' => $this->_tlabel.'description_id_exhib_stand'
        ]);

        if (!empty($this->_model->id_image)) {
            $imageDecorator = $fields['image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_model->id_image),
            ]);
        } else {
            $fields['image']->setRequired(true);
        }

        $this->_standLevelList = Doctrine_Query::create()
            ->from('StandLevel sl INDEXBY sl.id_stand_level')
            ->orderBy('sl.id_stand_level ASC')
            ->execute()
        ;

        $standLevelOptions = [];
        /** @var StandLevel $standLevel */
        foreach ($this->_standLevelList as $standLevel) {
            $standLevelOptions[$standLevel->getId()] = $standLevel->getName();
        }

        $fields['id_stand_level'] = $this->createElement('select', 'id_stand_level', [
            'label' => $this->_tlabel . 'id_stand_level',
            'multiOptions' => $standLevelOptions,
            'allowEmpty' => false,
            'value' => (int) $this->_model->id_stand_level,
            'validators' => [
                ['InArray', true, [array_keys($standLevelOptions)]],
            ],
        ]);

        $fields['data_icon'] = $this->createElement('textarea', 'data_icon', [
            'label' => $this->_tlabel . 'data_icon',
            'value' => $this->_model->data_icon,
            'filters' => ['StringTrim'],
        ]);

        $fields['data_stand'] = $this->createElement('textarea', 'data_stand', [
            'label' => $this->_tlabel . 'data_stand',
            'value' => $this->_model->data_stand,
            'filters' => ['StringTrim'],
        ]);
        // MAIN FIELDS - END

        // ASIDE FIELDS - START
        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];
        $aside['is_active'] = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_model->is_active,
            'validators' => [
                ['InArray', true, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $aside['is_public'] = $this->createElement('select', 'is_public', [
            'label' => $this->_tlabel . 'is_public',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_model->is_public,
            'validators' => [
                ['InArray',
                    true,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);
        // ASIDE FIELDS - END

        $this->addDisplayGroup($header, 'header');
        $this->addDisplayGroup($buttons, 'buttons');
        $this->addDisplayGroup($aside, 'aside');

        $this->addDisplayGroup($fields, 'main', ['legend' => $this->_tlabel . 'group_main']);
        $this->addDataBannerGroup();

        $this->setDecorators([
            'FormElements',
            ['OptionButton', [
                'value' => $this->getView()->translate('form-button-option_add-banner'),
                'class' => 'imageStandViewAddBanner',
                'block' => 'imageStandViewAddBanner',
            ]],
            'Form',
            ['FormError', ['placement' => 'PREPEND']],
        ]);
    }

    public function isValid($data)
    {
        foreach (array_keys($this->_subFormBanners) as $k) {
            $this->removeSubForm('banner-' . $k);
        }

        if (isset($data[$this->_belong_to]['data_banner'])) {
            foreach ($data[$this->_belong_to]['data_banner'] as $k => $v) {
                $this->_subFormBanners[$k] = new Event_Form_Admin_StandViewImage_Banner([
                    'data' => $v,
                    'key' => $k,
                ]);
                $this->_subFormBanners[$k]->setElementsBelongTo('data_banner[' . $k . ']');
                $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
            }
        }

        return parent::isValid($data);
    }

    /**
     * @param $model ExhibStandViewImage
     */
    protected function setModel($model)
    {
        $this->_model = $model;
        $this->_bannerData = $this->_model->getDataBanner();
    }

    protected function postIsValid($data)
    {
        $_data = $this->getValues($this->_belong_to);
        $this->_model->setName($this->getValue('name'));
        $this->_model->is_active = $this->getValue('is_active');
        $this->_model->is_public = $this->getValue('is_public');

        // ZAPIS PLIKÓW GRAFICZNYCH
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();
        if (isset($files['image']) && 0 === $files['image']['error']) {
            $image = Service_Image::createImage($this->_model, [
                'type' => Engine_Utils::_()->getFileExt($upload->getFileName('image', false)),
                'name' => $upload->getFileName('image', false),
                'source' => $upload->getFileName('image'),
            ]);
            $image->save();
            $this->_model->id_image = $image->getId();
        }

        $id_exhib_stand = $this->getValue('id_exhib_stand');
        if (!empty($id_exhib_stand)) {
            $exhibStand = ExhibStand::find($id_exhib_stand);
            if ($exhibStand) {
                $this->_model->ExhibStand = $exhibStand;
            }
        }

        $this->_model->id_stand_level = $this->getValue('id_stand_level');

//        var_dump($this->getValue('id_stand_level'));
//        var_dump($this->_standLevelList[$this->getValue('id_stand_level')]->toArray());
//        return true;

        $this->_model->data_icon = $this->getValue('data_icon');
        $this->_model->data_stand = $this->getValue('data_stand');

        $_data_banner = [];
        if (isset($_data['data_banner'])) {
            foreach ($_data['data_banner'] as $k => $v) {
                $_data_banner[$v['name']] = $v;
            }
        }

        $this->_model->setDataBanner($_data_banner);

        return true;
    }

    private function addDataBannerGroup()
    {
        foreach ($this->_bannerData as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_StandViewImage_Banner([
                'data' => $v,
                'key' => $k,
            ]);
            $this->_subFormBanners[$k]->setElementsBelongTo('data_banner[' . $k . ']');
            $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
        }
    }
}
