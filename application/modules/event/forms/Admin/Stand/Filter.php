<?php

class Event_Form_Admin_Stand_Filter extends Engine_FormAdmin
{
    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $name = $this->createElement('text', 'name', [
            'label' => 'Stand name',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

//        $stand_number_on_hall_2D = $this->createElement('text', 'stand_number_on_hall_2D', array(
//            'label' => 'Number on hall',
//            'decorators' => $this->elementDecoratorsCenturion,
//            'allowEmpty' => false,
//            'class' => ' field-text',
//            'filters'    => array('StringTrim')
//        ));

        $ExhibStandLevelOptions = ['' => ''];
        $AllExhibStandLevel = ExhibStandLevel::getAll();
        foreach ($AllExhibStandLevel as $exhib_stand_level) {
            $ExhibStandLevelOptions[$exhib_stand_level->getId()] = $exhib_stand_level->name;
        }

        $stand_level = $this->createElement('select', 'stand_level', [
            'label' => 'Stand type',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'multiOptions' => $ExhibStandLevelOptions,
        ]);

        $options = [
            '' => '',
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);

//        $isActiveVideochat = $this->createElement('select', 'is_active_videochat', array(
//            'label' => 'Is active videochat',
//            'multiOptions' => $listOptions,
//            'decorators' => $this->elementDecoratorsCenturion,
//            'allowEmpty' => false,
        //	    'class' => 'select-event-filter'
//        ));

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

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$name, $stand_level, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('HtmlTag');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
