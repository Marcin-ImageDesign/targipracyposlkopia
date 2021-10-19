<?php

class Event_Form_Type_Filter extends Engine_FormAdmin
{
    public function init()
    {
        $this->setAction('?page=1');

        $name = $this->createElement('text', 'name', [
            'label' => 'Title',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Search',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => 'Clear',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $options = [
            '' => '',
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);

        $isActive = $this->createElement('select', 'is_active', [
            'label' => 'Czy aktywny',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'select-event-sponsors-type-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$name, $isActive, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('HtmlTag');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
