<?php

class Event_Form_Admin_EventVideo_Filter extends Engine_Form
{
    protected $_tlabel = 'form_filter_event_standVideo_';

    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $video_name = $this->createElement('text', 'video_name', [
            'label' => $this->_tlabel . 'video_name',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $options = [
            '' => '',
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is_active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'select-active-filter',
            'item-class' => 'short_text-inline',
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

        $this->addDisplayGroup([$video_name, $is_active, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
