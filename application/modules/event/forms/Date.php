<?php

class Event_Form_Date extends Engine_FormAdmin
{
    /**
     * @var EventDate
     */
    private $eventDate;

    /**
     * @param EventDate $eventDate
     * @param array     $options
     */
    public function __construct($eventDate, $options = null)
    {
        parent::__construct($options);
        $this->eventDate = $eventDate;

        $vDate = new Zend_Validate_Date();
        $params = [['id_event', '=', $this->eventDate->id_event]];
        if (!$this->eventDate->isNew()) {
            $params[] = ['id_event_date', '!=', $this->eventDate->getId()];
        }

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('EventDate', 'date', $params);
        $vDate->setFormat('YYYY-MM-dd');

        $date = new ZendX_JQuery_Form_Element_DatePicker('date', [
            'label' => 'Date',
            'allowEmpty' => false,
            'decorators' => $this->elementDecoratorsJQueryHeader,
            'required' => true,
            'class' => 'field-text field-text-big',
            'filters' => ['StringTrim'],
            'validators' => [$vDate, $vAlreadyTaken],
            'value' => $this->eventDate->getDate(),
            'jQueryParams' => $this->jQueryDatepickerParams,
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $submit->getDecorator('row')->setOption('class', 'ui-button-big onRight');

        $this->setDecorators([
            'Description',
            'FormElements',
            'Form',
        ]);

        //----------------------------------------------
        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$date, $submit], 'header');

//        $main = new Zend_Form_SubForm();
//        $main->setDisableLoadDefaultDecorators(false);
//        $main->setDecorators($this->subFormDecoratorsCenturion);
//        $main->addElements(array($date, $submit));
        $main->addAttribs(['class' => 'form-header']);
        $group = $main->getDisplayGroup('header');
        $group->clearDecorators();
        $group->setAttrib('class', 'form-header');
//        $group->setLegend('Day of event');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => ' form-header']],
        ]);

        $main->addAttribs(['class' => 'form-main form-full-size']);

        //----------------------------------------------
        $this->addSubForm($main, 'main');
    }
}
