<?php

class Slider_Form_Filter extends Engine_Form
{
    public function init()
    {
        $this->setAction('?page=1');

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

        $listOptions = [
            '' => '',
            '0' => 'Nie',
            '1' => 'Tak',
        ];

        $isActive = $this->createElement('select', 'is_active', [
            'label' => 'Is active',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'select-slider-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);
        $translate = new Zend_View_Helper_Translate();
        $this->addDisplayGroup([$title, $isActive, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName($translate->translate('Filter'));
        $gDecorator = $group->getDecorator('HtmlTag');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
