<?php

class Event_Form_Admin_StandProduct extends Engine_FormAdmin
{
    /**
     * @var StandProduct
     */
    protected $_standProduct;

    protected $_belong_to = 'EventFormAdminStandProduct';

    protected $_tlabel = 'form_event-admin-stand-offer_';

    public function __construct($options = null)
    {
        $fields = null;
        parent::__construct($options);
        $this->setAttrib('ENCTYPE', 'multipart/form-data');

        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);

        $name = $this->createElement(
            'text',
            'name',
            [
                'label' => $this->_tlabel . 'name',
                'required' => true,
                'filters' => ['StringTrim'],
                'validators' => [$vStringLength],
                'allowEmpty' => false,
                'value' => $this->_standProduct->getName(),
            ]
        );

        $name->setDescription('Max. długość 255 znaków.');

        $fields['price'] = $this->createElement(
            'text',
            'price',
            [
                'label' => $this->_tlabel . 'price',
                'required' => false,
                // TODO: 'filters' => array('StringTrim'),
                'validators' => [$vStringLength],
                'allowEmpty' => true,
                'value' => $this->_standProduct->getPrice(),
            ]
        );
        $fields['originalprice'] = $this->createElement(
            'text',
            'original_price',
            [
                'label' => $this->_tlabel . 'original-price',
                'required' => false,
                // TODO: 'filters' => array('StringTrim'),
                'validators' => [$vStringLength],
                'allowEmpty' => true,
                'value' => $this->_standProduct->getOriginalPrice(),
            ]
        );

        $fields['unit'] = $this->createElement(
            'text',
            'unit',
            [
                'label' => $this->_tlabel . 'unit',
                'required' => false,
                'validators' => [
                    ['StringLength', true, ['max' => 32]],
                ],
                'allowEmpty' => true,
                'value' => $this->_standProduct->getUnit(),
            ]
        );

        $fields['unit']->setDescription('Max. długość 32 znaki.');

        $fields['image'] = $this->createElement('FileImage', 'image', [
            'label' => $this->_tlabel . 'image',
            'allowEmpty' => false,
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB',
        ]);

        if (!empty($this->_standProduct->id_image)) {
            $imageDecorator = $fields['image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_standProduct->id_image),
            ]);
        } else {
            $fields['image']->setRequired(true);
        }

        $fields['fb_image'] = $this->createElement('FileImage', 'fb_image', [
            'label' => $this->_tlabel . 'fb_image',
            'allowEmpty' => true,
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB',
        ]);

        if (!empty($this->_standProduct->id_fb_image)) {
            $imageDecorator = $fields['fb_image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_standProduct->id_fb_image),
            ]);
        }

        $fields['lead'] = $this->createElement('textarea', 'lead', [
            'label' => $this->_tlabel . 'lead',
            'required' => true,
            'value' => $this->_standProduct->getLead(),
        ]);

        $fields['keywords'] = $this->createElement('text', 'keywords', [
            'label' => $this->_tlabel . 'keywords',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_standProduct->getKeywords(),
            'validators' => [[$vStringLength, true]],
        ]);

        $fields['file'] = $this->createElement('file', 'file', [
            'label' => 'File',
            'allowEmpty' => true,
            'decorators' => $this->elementDecoratorsCenturionFile,
        ]);

        $fields['file']->addValidator('Extension', false, ALLOWED_FILE_EXTENSIONS);
        $fields['file']->addValidator('Count', false, 1);
        $fields['file']->addValidator('Size', false, MAX_FILE_SIZE);
        if ($this->_standProduct->getId()) {
            if (!$this->_standProduct->hasFile()) {
            } else {
                $fields['file']->setDescription($this->translate('Choose new file to exchange.'));
                $fileShowDecorator = $fields['file']->getDecorator('FileShow');
                $fileShowDecorator->setOptions([
                    'title' => 'pokaż plik',
                    'file' => $this->getView()->url(['hash' => $this->_standProduct->getFile()->getHash(), 'stand_hash' => $this->_standProduct->ExhibStand->getHash()], 'event_admin-stand-product-files_download'),
                ]);
            }
        }

        $fields['file_name'] = $this->createElement('text', 'file_name', [
            'label' => $this->_tlabel . 'file_name',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => '',
            'validators' => [[$vStringLength, true]],
        ]);
        if ($this->_standProduct->getId() && $this->_standProduct->hasFile()) {
            $fields['file_name']->setValue($this->_standProduct->getFile()->getTitle())
            ;
        }

        $fields['form_target'] = $this->createElement('text', 'form_target', [
            'label' => $this->_tlabel . 'form_target',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_standProduct->getFormTarget(),
        ]);

        $fields['offer_city'] = $this->createElement('text', 'offer_city', [
            'label' => $this->_tlabel . 'city',
            // 'description' => $this->_tlabel.'chat-group_description',
            'allowEmpty' => true,
            'required' => false,
            'placeholder' => $this->translate('Podaj miasto'),
            'value' => $this->_standProduct->getOfferCity(),
            'filters' => ['StringTrim'],
        ]);

        $fields['description'] = $this->createElement('wysiwyg', 'description', [
            'label' => $this->_tlabel . 'description',
            'editor' => 'full',
            'wrapper-class' => 'full',
            'required' => true,
            'value' => $this->_standProduct->getDescription(),
        ]);
        if ($this->_standProduct->ExhibStand->Event->getIsShopActive()) {
            $fields['link'] = $this->createElement('text', 'link', [
                'label' => $this->_tlabel . 'link',
                'required' => false,
                'value' => $this->_standProduct->getLink(),
                'filters' => ['StringTrim'],
            ]);
        }

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];
        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_standProduct->getIsActive(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $is_promotion = $this->createElement('select', 'is_promotion', [
            'label' => $this->_tlabel . 'is-promotion',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_standProduct->getIsPromotion(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $skip_offer_page = $this->createElement('select', 'skip_offer_page', [
            'label' => $this->_tlabel . 'skip_offer_page',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_standProduct->getSkipOfferPage(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$name], 'header');
        $this->addDisplayGroup([$submit], 'buttons');

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'legend' => $this->_tlabel . 'group_data',
            ]
        );

        $this->addDisplayGroup(
            [$is_active, $is_promotion, $skip_offer_page],
            'aside',
            [
                'legend' => $this->_tlabel . 'group_aside',
            ]
        );
    }

    public function isValid($data)
    {
        //walidacja zależna - pozwalamy zaznaczyć skip_offer_page, tylko jeśli form_target jest wypełniony
        $notEmpty = new Zend_Validate_NotEmpty();
        $notEmpty->setMessage('Jeśli opcja "Pomiń stronę oferty" jest aktywna, to pole jest wymagane');
        $form_target = $this->getElement('form_target');
        if (1 === $data['skip_offer_page']) {
            $form_target->addValidator($notEmpty, true)->setRequired(true);
        } else {
            $form_target->removeValidator($notEmpty)->setRequired(false);
        }

        return parent::isValid($data);
    }
}
