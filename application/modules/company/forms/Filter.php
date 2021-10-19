<?php

/**
 * Description of Filter.
 *
 * @author marcin
 */
class Company_Form_Filter extends Engine_Form
{
    protected $_tlabel = 'form_filter_company_';

    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $fields = [];

        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => false,
            'class' => 'field-text',
            'filters' => ['StringTrim'],
        ]);

        $fields['nip'] = $this->createElement('text', 'nip', [
            'label' => $this->_tlabel . 'nip',
            'required' => false,
            'class' => 'field-text',
            'filters' => ['StringTrim'],
        ]);

        $fields['krs'] = $this->createElement('text', 'krs', [
            'label' => $this->_tlabel . 'krs',
            'required' => false,
            'class' => 'field-text',
            'filters' => ['StringTrim'],
        ]);

        $fields['regon'] = $this->createElement('text', 'regon', [
            'label' => $this->_tlabel . 'regon',
            'required' => false,
            'class' => 'field-text',
            'filters' => ['StringTrim'],
        ]);

        $fields['representative'] = $this->createElement('text', 'representative', [
            'label' => $this->_tlabel . 'representative',
            'required' => false,
            'class' => 'field-text',
            'filters' => ['StringTrim'],
        ]);

        $listOptions = ['0' => 'label_no', '1' => 'label_yes'];
        $fields['is-active'] = $this->createElement('select', 'is_active', [
            'label' => 'cms-button_is-active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'select-event-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $fields['submit'] = $this->createElement('submit', 'submit', [
            'label' => 'cms-button_search',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $fields['clear'] = $this->createElement('submit', 'clear', [
            'label' => 'cms-button_clear',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->addDisplayGroup($fields, 'header', [
            'class' => 'box grid-filter',
        ]);
    }
}
