<?php

class Event_Form_Site_Element extends Engine_FormAdmin
{
    /**
     * @var EventSite Event Site
     */
    protected $eventSite;
    protected $_tlabel = 'label_event_site_';

//    /**
//     * Zend_Translate
//     */
//    protected $selectedLanguage;
//
//    /**
//     * Zend_Translate
//     */
//    protected $t;

    /**
     * @param EventSite $eventSite
     * @param array     $options
     */
    public function __construct($eventSite, $options = null)
    {// $selectedLanguage,
        parent::__construct($options);
        $this->eventSite = $eventSite;
//        $this->selectedLanguage = $selectedLanguage;
//        $this->t = Zend_Registry::get( 'Zend_Translate' );

        //dla zablokowanych stron dodawanych automatycznie dodajemy tłumaczenie
        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'filters' => ['StringTrim'],
            'value' => $eventSite->getTitle(), //$eventSite->isProtected() ? $this->translate($eventSite->title, $this->selectedLanguage->code) :
        ]);

        if ($eventSite->isProtected()) {
            $title->setRequired(false)->setAttrib('disable', 'disable');
        }

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $content = $this->createElement('wysiwyg', 'text', [
            'label' => 'Text',
            'editor' => 'full',
            'wrapper-class' => 'full',
            'value' => $eventSite->getContent(),
        ]);

        $optionsx = [
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($optionsx);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Visible',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => $eventSite->isActive(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptions)], ],
            ],
        ]);

        $this->addDisplayGroup([$title], 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup([$content], 'main', ['legend' => 'Content']);
        $this->addDisplayGroup(
            [$is_active],
            'aside',
            [
                'legend' => 'Options',
            ]
        );
    }
}
