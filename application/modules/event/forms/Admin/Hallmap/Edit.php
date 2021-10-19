<?php

class Event_Form_Admin_Hallmap_Edit extends Engine_Form
{
    protected $_belong_to = 'EventFormAdminHallmap';

    protected $_tlabel = 'form_event-admin-hallmap_';

    /**
     * @var EventHallMap
     */
    protected $hallmap;

    protected $_bannerData;

    private $_subFormBanners = [];

    public function init()
    {
        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');
        $this->addMainGroup();
        $this->addHallMapGroup();
        $this->setDecorators([
            'FormElements',
            ['OptionButton', [
                'value' => $this->getView()->translate('form-button-option_add-banner'),
                'class' => 'hallMapAddBanner',
                'block' => 'hallMapAddBanner',
            ]],
            'Form',
            ['FormError', ['placement' => 'PREPEND']],
        ]);
    }

    public function addMainGroup()
    {
        $fields = null;
        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->hallmap->getName(),
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $zoom_data = $this->hallmap->getZoomData();
        // var_dump($zoom_data);die;

        $fields['start_x'] = $this->createElement('text', 'start_x', [
            'label' => 'Start position x-axis',
            'required' => false,
            'allowEmpty' => true,
            'value' => $zoom_data['start_x'],
        ]);

        $fields['start_y'] = $this->createElement('text', 'start_y', [
            'label' => 'Start position y-axis',
            'required' => false,
            'allowEmpty' => true,
            'value' => $zoom_data['start_y'],
        ]);

        $fields['start_key'] = $this->createElement('text', 'start_key', [
            'label' => 'Start position - zoomed out/zoomed in',
            'required' => false,
            'allowEmpty' => true,
            'value' => $zoom_data['start_key'],
        ]);

        $fields['zoom_factor_1'] = $this->createElement('text', 'zoom_factor_1', [
            'label' => 'Zoomed out factor',
            'required' => false,
            'allowEmpty' => true,
            'value' => $zoom_data['zoom_factor_1'],
        ]);

        $fields['zoom_factor_2'] = $this->createElement('text', 'zoom_factor_2', [
            'label' => 'Zoomed in factor',
            'required' => false,
            'allowEmpty' => true,
            'value' => $zoom_data['zoom_factor_2'],
        ]);

        $fields['image'] = $this->createElement('FileImage', 'image', [
            'label' => $this->_tlabel . 'id_image',
            'allowEmpty' => false,
            'value' => $this->hallmap->getImage(),
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB',
        ]);

        if (!empty($this->hallmap->id_image)) {
            $imageDecorator = $fields['image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->hallmap->id_image),
            ]);
        } else {
            $fields['image']->setRequired(true);
        }

        $fields['promo_image'] = $this->createElement('FileImage', 'promo_image', [
            'label' => $this->_tlabel . 'promo_image',
            'allowEmpty' => true,
            'value' => $this->hallmap->getIdPromoPhoto(),
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB',
        ]);

        if (!empty($this->hallmap->id_promo_photo)) {
            $imageDecorator = $fields['promo_image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->hallmap->id_promo_photo),
            ]);
        }

        $fields['description'] = $this->createElement('wysiwyg', 'description', [
            'label' => $this->_tlabel . 'description',
            'editor' => 'full',
            'wrapper-class' => 'full',
            'value' => $this->hallmap->getDescription(),
        ]);

        $fields['order'] = $this->createElement('text', 'order', [
            'label' => $this->_tlabel . 'order',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->hallmap->getOrder(),
            'validators' => [
                ['Digits', true],
            ],
        ]);

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_info',
            ]
        );
    }

    public function addHallMapGroup()
    {
        if (!empty($this->_bannerData['items'])) {
            foreach ($this->_bannerData['items'] as $k => $v) {
                $this->_subFormBanners[$k] = new Event_Form_Admin_Hallmap_Banner([
                    'data' => $v,
                    'key' => $k,
                ]);
                $this->_subFormBanners[$k]->setElementsBelongTo('hall_map[' . $k . ']');
                $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
            }
        }
    }

    public function isValid($data)
    {
        // remove all old subforms
        foreach (array_keys($this->_subFormBanners) as $k) {
            $this->removeSubForm('banner-' . $k);
        }
        // create new list of subforms
        foreach ($data[$this->_belong_to]['hall_map'] as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_Hallmap_Banner(
                [
                    'data' => $v,
                    'key' => $k,
                ]
            );
            $this->_subFormBanners[$k]->setElementsBelongTo('hall_map[' . $k . ']');
            $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
        }

        return parent::isValid($data);
    }

    protected function setModel($model)
    {
        $this->hallmap = $model;
        $this->_bannerData = $this->hallmap->getHallMap();
    }

    protected function postIsValid($data)
    {
        $data_hall_map = $data[$this->getElementsBelongTo()]['hall_map'];
        if (!is_array($data_hall_map)) {
            $data_hall_map = json_decode($this->getValue('hall_map'), true);
        }
        // var_dump($data);die;
        $zoom = [
            'start_x' => $data[$this->getElementsBelongTo()]['start_x'],
            'start_y' => $data[$this->getElementsBelongTo()]['start_y'],
            'start_key' => $data[$this->getElementsBelongTo()]['start_key'],
            'zoom_factor_1' => $data[$this->getElementsBelongTo()]['zoom_factor_1'],
            'zoom_factor_2' => $data[$this->getElementsBelongTo()]['zoom_factor_2'],
        ];

        $this->hallmap->setZoomData($zoom);
        $data = $data[$this->_belong_to];
        $this->hallmap->setName($data['name']);
        $this->hallmap->setHallMap($data_hall_map);
        $this->hallmap->setDescription($data['description']);
        if (!empty($data['order'])) {
            $this->hallmap->setOrder($data['order']);
        } else {
            $this->hallmap->setOrder(null);
        }

        return true;
    }
}
