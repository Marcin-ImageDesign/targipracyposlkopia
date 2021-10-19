<?php

class Event_Form_Type_Element extends Engine_FormAdmin
{
    public function init()
    {
        $this->setAttrib('ENCTYPE', 'multipart/form-data');
        //$vAlreadyTaken = new Engine_Validate_AlreadyTaken();

        $name = $this->createElement('text', 'name', [
            'label' => 'Title',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);

        $name->setDescription($this->translate('Title'));

        $options = [
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);
        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Is active',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptions)], ],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->setDecorators([
            'Description',
            'FormElements',
            'Form',
        ]);

        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$name, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        $aside = new Zend_Form_SubForm();
        $aside->setDisableLoadDefaultDecorators(false);
        $aside->setDecorators($this->subFormDecoratorsCenturion);
        $aside->addDisplayGroup([$is_active], 'aside');

        $groupAside = $aside->getDisplayGroup('aside');
        $groupAside->clearDecorators();
        $groupAside->setLegend('Opcje');
        $groupAside->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $aside->addAttribs(['class' => 'form-aside']);
        $aside->addAttribs(['style' => 'float:right']);

        $this->addSubForm($header, 'header');
        $this->addSubForm($aside, 'aside');
    }
}
