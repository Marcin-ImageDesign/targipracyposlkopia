<?php

class Event_Form_Admin_EventVideo extends Engine_Form
{
    protected $_eventVideo;
    protected $_belong_to = 'EventVideo';

    protected $_tlabel = 'label_form_event-admin-stand-video';

    public function __construct($eventVideo, $options = null)
    {
        $this->_eventVideo = $eventVideo;

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
                'value' => $this->_eventVideo->getName(),
            ]
        );
        $name->setDescription('Nazwa. Max. długość 255 znaków.');

        $lead = $this->createElement('textarea', 'lead', [
            'label' => $this->_tlabel . '_lead',
            'class' => 'field-text',
            'editor' => 'full',
            'style' => 'width: 415px; height: 200px',
            'value' => $this->_eventVideo->getLead(),
        ]);

        $video_link = $this->createElement('text', 'video_link', [
            'label' => $this->_tlabel . '_video-link',
            'class' => 'field-text',
            'editor' => 'full',
            'style' => 'width: 415px',
            'value' => $this->_eventVideo->getVideoLink(),
        ]);

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . '_is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_eventVideo->getIsActive(),
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
            [$is_active],
            'aside',
            [
                'legend' => $this->_tlabel . '_legend-options',
            ]
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_eventVideo->setName($data['name']);
        $this->_eventVideo->setLead($data['lead']);
        $this->_eventVideo->setVideoLink($data['video_link']);
        $this->_eventVideo->setIsActive($data['is_active']);

        return true;
    }
}
