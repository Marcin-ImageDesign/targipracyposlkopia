<?php

class Event_Form_User_Filter extends Engine_Form
{
    public function init()
    {
        $this->setAction('?page=1');

        $user = $this->createElement('text', 'user', [
            'label' => 'User',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $event = $this->createElement('text', 'event', [
            'label' => 'Event',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $listOptions = [
            '' => '',
            '0' => 'No',
            '1' => 'Yes',
        ];

        $isConfirm = $this->createElement('select', 'is_confirm', [
            'label' => 'Is confirm',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'select-filter',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Search',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => 'Clear',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$user, $event, $isConfirm, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('HtmlTag');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
