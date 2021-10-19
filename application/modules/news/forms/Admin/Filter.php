<?php

class News_Form_Admin_Filter extends Engine_FormAdmin
{
    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $title = $this->createElement('text', 'title', [
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
            'label' => 'Visible',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'select-filter',
        ]);

        $isPromoted = $this->createElement('select', 'is_promoted', [
            'label' => 'Promoted',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'select-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$title, $isActive, $isPromoted, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
