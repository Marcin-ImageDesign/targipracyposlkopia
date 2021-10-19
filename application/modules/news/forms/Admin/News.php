<?php

class News_Form_Admin_News extends Engine_Form
{
    /**
     * @var News
     */
    protected $_news;

    protected $_eventId;

    protected $_belong_to = 'NewsFormAdminNews';

    protected $_tlabel = 'form_news_admin_news_';

    /**
     * @param News  $news
     * @param array $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        // $vAlreadyTaken = new Engine_Validate_AlreadyTaken();
        $vDate = new Zend_Validate_Date();
        $vDate->setFormat('YYYY-MM-dd');

        $title = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'required' => true,
            'allowEmpty' => false,
            'validators' => [],
            'value' => $this->_news->getTitle(),
            'filters' => ['StringTrim'],
        ]);

        $lead = $this->createElement('textarea', 'lead', [
            'label' => $this->_tlabel . 'short-text',
            'allowEmpty' => false,
            'value' => $this->_news->getLead(),
            'filters' => ['StringTrim'],
        ]);

        $eventArray = [];

        $eventList = Doctrine_Query::create()
            ->from('Event e')
            ->leftJoin('e.Translations et')
            ->where('e.id_base_user = ?', $this->_news->id_base_user)
            ->execute()
        ;

        foreach ($eventList as $event) {
            $eventArray[$event->getId()] = $event->getTitle();
            if (!empty($this->_news->getIdEvent())) {
                if ($this->_news->getIdEvent() !== $event->getId()) {
                    unset($eventArray[$event->getId()]);
                }
            } elseif (!empty($this->_eventId)) {
                if ($this->_eventId !== $event->getId()) {
                    unset($eventArray[$event->getId()]);
                }
            }
        }

        $event = $this->createElement('select', 'event', [
            'label' => $this->_tlabel . 'event',
            'multiOptions' => $eventArray,
            'allowEmpty' => false,
            'value' => $this->_news->getIdEvent() ? $this->_news->getIdEvent() : $this->_eventId ? $this->_eventId : '',
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($eventArray)], ],
            ],
        ]);
        // if (!empty($this->_eventId)) {
        //     // $event->setAttribs(array('disable' => 'disable'));
        // }

        $standList = Doctrine_Query::create()
            // ->select('es.hash, est.name')
            ->from('ExhibStand es')
            ->leftJoin('es.Translations est')
            ->where('es.id_base_user = ?', $this->_news->id_base_user)
            ;

        if (!empty($this->_news->getIdEvent())) {
            $standList->andWhere('es.id_event = ?', $this->_news->getIdEvent());
        } elseif (!empty($this->_eventId)) {
            $standList->andWhere('es.id_event = ?', $this->_eventId);
        }
        $standList = $standList->execute();

        $standTranslationList = Doctrine_Query::create()
            // ->select('es.hash, est.name')
            ->from('ExhibStandTranslation es')
            ->execute()
            ;

        $standTranslationArray = ['0' => ''];

        foreach ($standTranslationList as $key => $standTranslation) {
            if (!empty($this->_eventId) || !empty($this->_news->getIdEvent())) {
                $standTranslationArray[$standTranslation->getStandId()] = $standTranslation->getStandName();
            } else {
                $standTranslationArray[$standTranslation->getStandId()] = $standTranslation->getEventName() . ': ' . $standTranslation->getStandName();
            }
        }

        $standArray = ['0' => ''];
        foreach ($standList as $key => $stand) {
            $standArray[$stand->getId()] = $stand->getId();
        }

        $standArray = array_intersect_key($standTranslationArray, $standArray);
        $stand = $this->createElement('select', 'stand', [
            'label' => $this->_tlabel . 'stand',
            'multiOptions' => $standArray,
            'allowEmpty' => false,
            'value' => $this->_news->getIdStand() ? $this->_news->getIdStand() : 0,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($standArray)], ],
            ],
        ]);

        $image = $this->createElement('FileImage', 'image', [
            'label' => $this->_tlabel . 'image',
            'allowEmpty' => false,
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS,
        ]);

        if (!empty($this->_news->id_image)) {
            $imageDecorator = $image->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_news->id_image),
            ]);
        } else {
            $image->setRequired(true);
        }

        $image_home = $this->createElement('FileImage', 'image_home', [
            'label' => $this->_tlabel . 'image_home',
            'allowEmpty' => false,
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS,
        ]);

        $image_home->getDecorator('row')->setOption('class', 'form-item image_home_class');

        if (!empty($this->_news->id_image_home)) {
            $imageDecorator = $image_home->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_news->id_image_home),
            ]);
        } else {
            $image_home->setRequired(true);
        }

        $link_outside = $this->createElement('text', 'link_outside', [
            'label' => $this->_tlabel . 'link_outside',
            'allowEmpty' => true,
            'required' => false,
            'value' => $this->_news->getLinkOutside(),
            'filters' => ['StringTrim'],
            'description' => $this->getView()->translate('label_form_news_with_http'),
        ]);

        $text = $this->createElement('wysiwyg', 'text', [
            'label' => $this->_tlabel . 'content',
            'editor' => 'full',
            'wrapper-class' => 'full',
            'required' => true,
            'value' => $this->_news->getText(),
            'filters' => ['StringTrim'],
        ]);

        $publication_start = $this->createElement('DatePicker', 'publication_start', [
            'label' => $this->_tlabel . 'pub-start',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
            'value' => $this->_news->getPublicationStart(),
        ]);

        $publication_end = $this->createElement('DatePicker', 'publication_end', [
            'label' => $this->_tlabel . 'pub-end',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
            'value' => $this->_news->getPublicationEnd(),
        ]);

        $metatag_title = $this->createElement('text', 'metatag_title', [
            'label' => $this->_tlabel . 'seo-title',
            'allowEmpty' => false,
            'value' => $this->_news->getMetatagTitle(),
            'filters' => ['StringTrim'],
        ]);

        $metatag_key = $this->createElement('text', 'metatag_key', [
            'label' => $this->_tlabel . 'seo-keywords',
            'allowEmpty' => false,
            'value' => $this->_news->getMetatagKey(),
        ]);

        $metatag_desc = $this->createElement('text', 'metatag_desc', [
            'label' => $this->_tlabel . 'seo-desc',
            'allowEmpty' => false,
            'value' => $this->_news->getMetatagDesc(),
            'filters' => ['StringTrim'],
        ]);

        $metatag_title->getDecorator('row')->setOption('class', 'form-item metatag_title_content');
        $metatag_key->getDecorator('row')->setOption('class', 'form-item metatag_key_content');
        $metatag_desc->getDecorator('row')->setOption('class', 'form-item metatag_desc_content');

        $is_metatag = $this->createElement('checkbox', 'is_metatag', [
            'label' => $this->_tlabel . 'seo-active',
            'uncheckedValue' => '0',
            'class' => 'seoActive',
            'onclick' => 'seoActive()',
            'value' => $this->_news->getIsMetatag(),
        ]);

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => $this->_news->is_active,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $on_homepage = $this->createElement('select', 'on_homepage', [
            'label' => $this->_tlabel . 'homepage',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher onHomePage',
            'value' => $this->_news->on_homepage,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $chat_schedule = $this->createElement('select', 'chat_schedule', [
            'label' => $this->_tlabel . 'chat_schedule',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => $this->_news->chat_schedule,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        // $is_promoted = $this->createElement('select', 'is_promoted', array(
        //     'label' => $this->_tlabel .'promoted',
        //     'multiOptions' => $listOptionsNoYes,
        //     'allowEmpty' => false,
        //     'class' => 'field-switcher',
        //     'value' => $this->_news->is_promoted,
        //     'validators' => array(
        //     array('InArray',
        //         false,
        //         array(array_keys($listOptionsNoYes)))
        //     )
        // ));

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'save',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->addDisplayGroup([$title], 'header');
        $this->addDisplayGroup([$submit], 'buttons');

        $this->addDisplayGroup(
            [$publication_start, $publication_end, $event, $stand, $image, $image_home, $link_outside, $lead, $text],
            'main',
            [
                'legend' => $this->_tlabel . 'group_data',
            ]
        );

        $this->addDisplayGroup(
            [$is_active, $on_homepage, $chat_schedule],
            'aside', // $is_promoted
            [
                'legend' => $this->_tlabel . 'group_aside',
            ]
        );

        $this->addDisplayGroup(
            [$is_metatag, $metatag_title, $metatag_key, $metatag_desc],
            'seo',
            [
                'legend' => $this->_tlabel . 'group_seo',
                'class' => 'group-wrapper group-main',
            ]
        );
    }

    public function isValid($data)
    {
        if (0 === $data[$this->_belong_to]['on_homepage']) {
            $this->getElement('image_home')->setRequired(false);
        }

        return parent::isValid($data);
    }

    public function render(Zend_View_Interface $view = null)
    {
        $is_metatag = $this->is_metatag->getValue();
        $metatag_title = $this->getElement('metatag_title');
        $metatag_key = $this->getElement('metatag_key');
        $metatag_desc = $this->getElement('metatag_desc');

        if (0 === $is_metatag) {
            $metatag_title->getDecorator('row')->setOption('style', 'display: none');
            $metatag_key->getDecorator('row')->setOption('style', 'display: none');
            $metatag_desc->getDecorator('row')->setOption('style', 'display: none');
        }

        $on_homepage = $this->getElement('on_homepage')->getValue();
//        $chat_schedule = $this->getElement('chat_schedule')->getValue();
        $image_home = $this->getElement('image_home');

        if (0 === $on_homepage) {
            $image_home->getDecorator('row')->setOption('style', 'display: none');
        }

        return parent::render();
    }

    protected function postIsValid($data)
    {
        $utils = Engine_Utils::getInstance();
        $data = $data[$this->_belong_to];

        $this->_news->setTitle($data['title']);
        $this->_news->setLinkOutside($data['link_outside']);
        $this->_news->setLead($data['lead']);
        $this->_news->setText($data['text']);
        $this->_news->setPublicationStart($data['publication_start']);
        $this->_news->setPublicationEnd($data['publication_end']);
        $this->_news->setIsActive($data['is_active']);
   //     $this->_news->setIsPromoted($data['is_promoted']);
        $this->_news->on_homepage = $data['on_homepage'];
        $this->_news->chat_schedule = $data['chat_schedule'];
        $this->_news->setIdEvent($data['event']);

        $this->_news->setIdStand($data['stand']);
        $this->_news->setUri($utils->getFriendlyUri($data['title']));

        $this->_news->setIsMetatag($data['is_metatag']);
        $this->_news->setMetatagTitle($data['metatag_title']);
        $this->_news->setMetatagKey($data['metatag_key']);
        $this->_news->setMetatagDesc($data['metatag_desc']);

        return true;
    }
}
