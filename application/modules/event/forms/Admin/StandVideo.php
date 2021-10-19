<?php

class Event_Form_Admin_StandVideo extends Engine_Form
{
    protected $_standVideo;
    protected $_belong_to = 'StandVideo';

    protected $_tlabel = 'label_form_event-admin-stand-video';

    public function __construct($standVideo, $options = null)
    {
        $this->_standVideo = $standVideo;

        parent::__construct($options);
        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);

        $name = $this->createElement(
            'text',
            'name',
            [
                'label' => $this->_tlabel . '_name',
                'required' => true,
                'filters' => ['StringTrim'],
                'validators' => [$vStringLength],
                'class' => 'field-text field-text-big',
                'allowEmpty' => false,
                'value' => $this->_standVideo->getName(),
            ]
        );
        $name->setDescription('Nazwa. Max. długość 255 znaków.');

        $lead = $this->createElement('textarea', 'lead', [
            'label' => $this->_tlabel . '_lead',
            'class' => 'field-text',
            'editor' => 'full',
            'style' => 'width: 415px; height: 200px',
            'value' => $this->_standVideo->getLead(),
        ]);

        $video_link = $this->createElement('text', 'video_link', [
            'label' => $this->_tlabel . '_video-link',
            'class' => 'field-text',
            'editor' => 'full',
            'style' => 'width: 415px',
            'value' => $this->_standVideo->getVideoLink(),
        ]);

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . '_is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_standVideo->getIsActive(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $show_on_stand = $this->createElement('select', 'show_on_stand', [
            'label' => $this->_tlabel . '_show_on_stand',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_standVideo->getShowOnstand(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptionsNoYes)], ],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . '_save',
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->addDisplayGroup([$name], 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup([$lead, $video_link], 'main', ['legend' => $this->_tlabel . '_legend-data']);
        $this->addDisplayGroup(
            [$is_active, $show_on_stand],
            'aside',
            [
                'legend' => $this->_tlabel . '_legend-options',
            ]
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_standVideo->setName($data['name']);
        $this->_standVideo->setLead($data['lead']);
        $this->_standVideo->setVideoLink($data['video_link']);
        $this->_standVideo->setIsActive($data['is_active']);
        $this->_standVideo->setShowOnStand($data['show_on_stand']);

        return true;
    }
}
