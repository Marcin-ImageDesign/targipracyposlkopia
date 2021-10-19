<?php

class Statistics_Form_Filter extends Engine_Form
{
    protected $_tlabel = 'form_filter_statistics_';

    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $date_from = $this->createElement('DatePicker', 'date_from', [
            'label' => $this->_tlabel . 'date_from',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
        ]);

        $date_to = $this->createElement('DatePicker', 'date_to', [
            'label' => $this->_tlabel . 'date_to',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd H:m:s'])],
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

        $this->addDisplayGroup([$date_from, $date_to, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
