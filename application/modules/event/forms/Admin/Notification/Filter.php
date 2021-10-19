<?php

class Event_Form_Admin_Notification_Filter extends Engine_Form
{
    protected $_tlabel = 'form_filter_event_notifications_';

    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $date_start = $this->createElement('DatePicker', 'notification_date_from', [
            'label' => 'Date start',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
        ]);

        $date_end = $this->createElement('DatePicker', 'notification_date_to', [
            'label' => 'Date end',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'search',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => $this->_tlabel . 'clear',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$date_start, $date_end, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
