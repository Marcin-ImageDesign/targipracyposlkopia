<?php

class Event_Form_Admin_Notification extends Engine_Form
{
    protected $_notification;
    protected $_belong_to = 'Notification';

    protected $_tlabel = 'label_form_event-admin-notification_';

    public function __construct($notification, $options = null)
    {
        $this->_notification = $notification;

        parent::__construct($options);
        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);

        $name = $this->createElement(
            'text',
            'name',
            [
                'label' => $this->_tlabel . 'name',
                'required' => true,
                'filters' => ['StringTrim'],
                'validators' => [$vStringLength],
                'class' => 'field-text field-text-big',
                'allowEmpty' => false,
                'value' => $this->_notification->getName(),
            ]
        );
        $name->setDescription('Nazwa. Max. długość 255 znaków.');

        $notification_date = $this->createElement('DateTimePicker', 'notification_date', [
            'label' => $this->_tlabel . 'date',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'jQueryParams' => ['dateFormat' => 'yy-mm-dd H:i'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd H:i'])],
            'value' => $this->_notification->getDateFormat(),
        ]);

        $notification_text = $this->createElement('textarea', 'notification_text', [
            'label' => $this->_tlabel . 'text',
            'class' => 'field-text',
            'editor' => 'full',
            'style' => 'width: 415px; height: 200px',
            'value' => $this->_notification->getNotificationText(),
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'save',
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->addDisplayGroup([$name], 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup([$notification_date, $notification_text], 'main', ['legend' => $this->_tlabel . 'legend-data']);
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_notification->setName($data['name']);
        $this->_notification->setNotificationDate($data['notification_date']);
        $this->_notification->setNotificationText($data['notification_text']);

        return true;
    }
}
