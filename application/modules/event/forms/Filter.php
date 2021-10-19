<?php

class Event_Form_Filter extends Engine_Form
{
    protected $_tlabel = 'form_event-admin-filter_';

    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $title = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
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

        $listOptionsNoYes = ['' => '', '0' => 'label_form_no', '1' => 'label_form_yes'];
        $isActive = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'item-class' => 'short_text-inline ',
            'class' => 'select-user-filter select-filter',
        ]);

        $isArchive = $this->createElement('select', 'is_archive', [
            'label' => $this->_tlabel . 'is-archive',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'item-class' => 'short_text-inline',
            'class' => 'select-event-filter select-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$title, $isActive, $isArchive, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName($this->_tlabel . 'filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
