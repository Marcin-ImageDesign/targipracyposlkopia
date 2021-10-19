<?php

class Event_Form_Element extends Engine_Form
{
    protected $_belong_to = 'EventFormElement';

    protected $_tlabel = 'form_event-admin_';

    /**
     * @var BaseUser
     */
    protected $baseUser;

    /**
     * @var Event
     */
    protected $_event;

    /**
     * @param Event $event
     * @param array $options
     */
    public function init()
    {
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken();
        $vDay = new Engine_Validate_DateStartEndCompare();

        $fileDescriptionSize = $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB';

        $fields = [];
        $title = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_event->getTitle(),
        ]);

        $title->setDescription('Nazwa. Max. długość 255 znaków.');

        $languages = [];
        $baseUser = Zend_Registry::get('BaseUser');

        if ($baseUser instanceof BaseUser) {
            $langs = $baseUser->getBaseUserLanguage();
            foreach ($langs as $lang) {
                if ($lang instanceof Language) {
                    $languages[$lang->getId()] = $lang->getOriginalName();
                }
            }
        }

        $fields['language'] = $this->createElement('select', 'language', [
            'label' => $this->_tlabel . 'language',
            'required' => true,
            'filters' => ['StringTrim'],
            'value' => $baseUser->getTranslations()->getFirst()->getId(),
            'multiOptions' => $languages,
            'validators' => [
                [
                    'InArray',
                    false,
                    [array_keys($languages)],
                ],
            ],
        ]);

        $fields['uri'] = $this->createElement('text', 'uri', [
            'label' => $this->_tlabel . 'uri',
            'required' => true,
            'allowEmpty' => false,
            'validators' => [
                [$vAlreadyTaken, true],
            ],
            'value' => $this->_event->getUri(),
        ]);

        $fields['uri']->setDescription('Nazwa. Max. długość 255 znaków.');

        $fields['dateStart'] = $this->createElement('DatePicker', 'date_start', [
            'label' => $this->_tlabel . 'date-start',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
            'value' => $this->_event->getDateStartFormat(),
        ]);

        $fields['dateEnd'] = $this->createElement('DatePicker', 'date_end', [
            'label' => $this->_tlabel . 'date-end',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd']), $vDay],
            'value' => $this->_event->getDateEndFormat(),
        ]);

        //logo na hali
        $fields['image'] = $this->createElement('FileImage', 'image', [
            'label' => $this->_tlabel . 'image',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        if (!empty($this->_event->id_image)) {
            $imageDecorator = $fields['image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_event->id_image),
            ]);
        }

        $fields['replace_text'] = $this->createElement('text', 'replace_text', [
            'label' => $this->_tlabel . 'replace_text',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->_event->getReplaceText(),
        ]);

        $fields['replace_text']->setDescription('Na hali zamiast logotypu');

        $fields['replace_text_class'] = $this->createElement('text', 'replace_text_class', [
            'label' => $this->_tlabel . 'replace_text_class',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->_event->getReplaceTextClass(),
        ]);

        $fields['replace_text_class']->setDescription('Klasa css kontenera z tekstem');

        // miniatura targów do udostępnienia wydarzenia
        $fields['event_miniature'] = $this->createElement('FileImage', 'event_miniature', [
            'label' => $this->_tlabel . 'event_miniature',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        if (!empty($this->_event->id_event_miniature)) {
            $imageDecorator = $fields['event_miniature']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_event->id_event_miniature),
            ]);
        } else {
            $fields['event_miniature']->setRequired(true);
        }

        //tło eventu
        $fields['bg_image'] = $this->createElement('FileImage', 'bg_image', [
            'label' => $this->_tlabel . 'bg_image',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        if (!empty($this->_event->bg_id_image)) {
            $imageDecorator = $fields['bg_image']->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'id_image' => $this->_event->bg_id_image,
                'crop' => true,
                'delete' => [
                    'route' => 'admin',
                    'params' => [],
                    'onClickConfirm' => 'Are you sure you want to delete this file?',
                ],
            ]);
        }

        $fields['bg_color'] = $this->createElement('text', 'bg_color', [
            'label' => $this->_tlabel . 'bg_color',
            'value' => $this->_event->getBgColor(),
        ]);

        $fields['email_for_order'] = $this->createElement('text', 'email_for_order', [
            'label' => $this->_tlabel . 'email_for_order',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_event->getEmailForOrder(),
        ]);

        $fields['style'] = $this->createElement('textarea', 'style', [
            'label' => $this->_tlabel . 'style',
            'style' => 'width: 415px; height: 200px',
            'value' => $this->_event->getStyle(),
        ]);

        $fields['lead'] = $this->createElement('wysiwyg', 'lead', [
            'label' => $this->_tlabel . 'lead',
            'editor' => 'full',
            'wrapper-class' => 'full',
            'value' => $this->_event->getLead(),
        ]);

        $fields['home_page_url'] = $this->createElement('text', 'home_page_url', [
            'label' => $this->_tlabel . 'home_page_url',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_event->getHomePageUrl(),
        ]);

        $fields['fb_app_id'] = $this->createElement('text', 'fb_app_id', [
            'label' => $this->_tlabel . 'fb_app_id',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_event->getFbAppId(),
        ]);
        $fields['fb_secret'] = $this->createElement('text', 'fb_secret', [
            'label' => $this->_tlabel . 'fb_secret',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_event->getFbSecret(),
        ]);

        $listOptions = ['0' => 'label_form_no', '1' => 'label_form_yes'];
        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray',
                 false,
                 [array_keys($listOptions)],],
            ],
            'value' => $this->_event->is_active,
        ]);

        $is_scheduled = $this->createElement('select', 'is_scheduled', [
            'label' => $this->_tlabel . 'is-scheduled',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray',
                 false,
                 [array_keys($listOptions)],],
            ],
            'value' => $this->_event->is_scheduled,
        ]);

        $is_login_required = $this->createElement('select', 'is_login_required', [
            'label' => $this->_tlabel . 'is_login_required',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray', true, [array_keys($listOptions)]],
            ],
            'value' => $this->_event->is_login_required,
        ]);

        $is_shop_active = $this->createElement('select', 'is_shop_active', [
            'label' => $this->_tlabel . 'is_shop_active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray', true, [array_keys($listOptions)]],
            ],
            'value' => $this->_event->is_shop_active,
        ]);

        $is_reception_active = $this->createElement('select', 'is_reception_active', [
            'label' => $this->_tlabel . 'is_reception_active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray', true, [array_keys($listOptions)]],
            ],
            'value' => $this->_event->is_reception_active,
        ]);

        $is_slider_on = $this->createElement('select', 'is_slider_on', [
            'label' => $this->_tlabel . 'is_slider_on',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray', true, [array_keys($listOptions)]],
            ],
            'value' => $this->_event->is_slider_on,
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'cms-label_save',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->addDisplayGroup([$title], 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup(
            [$is_active, $is_scheduled, $is_login_required, $is_shop_active, $is_reception_active, $is_slider_on],
            'aside',
            [
                'legend' => $this->_tlabel . 'group_options',
            ]
        );

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'legend' => $this->_tlabel . 'group_main',
            ]
        );
    }

    public function addBrandToForm()
    {
        $brandList = Doctrine_Query::create()
            ->from('Brand b')
            ->innerJoin('b.Translations bt WITH bt.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('b.ChildBrands cb WITH cb.is_active = 1')
            ->leftJoin('cb.Translations cbt WITH cbt.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('cb.ChildBrands cb1 WITH cb1.is_active = 1')
            ->leftJoin('cb1.Translations cbt1 WITH cbt1.id_language = ?', Engine_I18n::getLangId())
            ->where('b.is_active = 1 AND b.id_brand_parent IS NULL')
            ->orderBy('bt.name ASC, cbt.name ASC, cbt1.name ASC')
            ->execute();

        $eventHasBrand = $this->_event->EventHasBrand->toKeyValueArray('id_brand', 'id_brand');
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

        $brandElement = $this->createElement('multiFieldSelect', 'event_has_brand', [
            'label' => $this->_tlabel . 'brand',
            'required' => false,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'value' => $eventHasBrand,
            'multiOptions' => $multiOptions,
            'label_add-item' => $this->_tlabel . 'add-brand',
        ]);

        $this->addDisplayGroup(
            [$brandElement],
            'brand',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_brand',
            ]
        );
    }

    public function populateBrandForm()
    {
        $this->_event->EventHasBrand->clear();
        $brandField = array_flip((array)$this->event_has_brand->getValue());
        foreach (array_keys($brandField) as $id_brand) {
            if (!empty($id_brand)) {
                $this->addChildrenBrand($id_brand);
                $eventHasBrand = new EventHasBrand();
                $eventHasBrand->id_brand = $id_brand;
                $this->_event->EventHasBrand->add($eventHasBrand);
            }
        }
    }

    public function addSEOGroup()
    {
        $fields = [];

        $fields[] = $this->createElement('text', 'seo_title', [
            'label' => $this->_tlabel . 'seo_title',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->_event->getField('seo_title'),
            'description' => 'Max. długość 255 znaków.',
        ]);

        $fields[] = $this->createElement('text', 'seo_keywords', [
            'label' => $this->_tlabel . 'seo_keywords',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->_event->getField('seo_keywords'),
            'description' => 'Max. długość 255 znaków.',
        ]);

        $fields[] = $this->createElement('text', 'seo_description', [
            'label' => $this->_tlabel . 'seo_description',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->_event->getField('seo_description'),
            'description' => 'Max. długość 255 znaków.',
        ]);

        $this->addDisplayGroup(
            $fields,
            'seo',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'SEO',
            ]
        );
    }

    public function addOfferGroup()
    {
        $this->addDisplayGroup(
            [
                $this->createElement('text', 'offer_password', [
                    'label' => $this->_tlabel . 'offer_password',
                    'required' => false,
                    'allowEmpty' => true,
                    'value' => $this->_event->offer_password,
                    'description' => $this->_tlabel . 'offer_password_description',
                ]),
            ],
            'offer_group',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'offer_group',
            ]
        );
    }

    /**
     * @param Event $event
     */
    protected function setEvent($event)
    {
        $this->_event = $event;
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->getElementsBelongTo()];

        //Przypisanie pol z formularza do elemntów obiektu
        $this->_event->is_active = (bool)$data['is_active'];
        $this->_event->is_scheduled = (bool)$data['is_scheduled'];
        $this->_event->is_login_required = (bool)$data['is_login_required'];
        $this->_event->is_shop_active = (bool)$data['is_shop_active'];
        $this->_event->is_reception_active = (bool)$data['is_reception_active'];
        $this->_event->is_slider_on = (bool)$data['is_slider_on'];
        $this->_event->setTitle($data['title']);
        $this->_event->setBgColor($data['bg_color']);
        $this->_event->setEmailForOrder($data['email_for_order']);
        $this->_event->setReplaceText($data['replace_text']);
        $this->_event->setReplaceTextClass($data['replace_text_class']);

        if (isset($data['style'])) {
            $this->_event->setStyle($data['style']);
        }
        $this->_event->setLead($data['lead']);
        $this->_event->setHomePageUrl($this->getValue('home_page_url'));

        $this->_event->setFbAppId($data['fb_app_id']);
        $this->_event->setFbSecret($data['fb_secret']);

        if (!empty($data['date_start'])) {
            $this->_event->setDateStart($data['date_start']);
        } else {
            $this->_event->setDateStart(null);
        }
        if (!empty($data['date_end'])) {
            $this->_event->setDateEnd($data['date_end']);
        } else {
            $this->_event->setDateEnd(null);
        }

        // zapisanie informacji o SEO
        $this->_event->setField('seo_title', $data['seo_title']);
        $this->_event->setField('seo_keywords', $data['seo_keywords']);
        $this->_event->setField('seo_description', $data['seo_description']);

        $this->_event->setOfferPassword($data['offer_password'] ?: null);

        $this->_event->setUri(Engine_Utils::_()->getFriendlyUri($data['uri']));

        $baseUser = Zend_Registry::get('BaseUser');
        $baseUser->id_language = $data['language'];
        $baseUser->save();

        return true;
    }

    private function addChildrenBrand($id_brand)
    {
        $brandField = (array)$this->event_has_brand->getValue();

        $childrenBrand = Brand::getBrandChildren($id_brand);
        if ($childrenBrand) {
            foreach ($childrenBrand as $id_child) {
                //arleady on list
                if (in_array($id_child, $brandField, true)) {
                    continue;
                }
                $eventHasBrand = new EventHasBrand();
                $eventHasBrand->id_brand = $id_child;
                $this->_event->EventHasBrand->add($eventHasBrand);

                $this->addChildrenBrand($id_child);
            }
        }
    }
}
