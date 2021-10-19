<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Banner.
 *
 * @author marek
 */
class Event_Form_Admin_Hallmap_Banner extends Engine_Form
{
    // Variables
    protected $_tlabel = 'label_form_hall-map-banner_';

    protected $_data;
    protected $_key;

    public function init()
    {
        $fields = [];

        $fields['shape'] = $this->createElement('hidden', 'shape', [
            'value' => 'poly',
        ]);

        $fields['title'] = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['title'],
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $fields['alt'] = $this->createElement('text', 'alt', [
            'label' => $this->_tlabel . 'alt',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['alt'],
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);
        $fields['coords'] = $this->createElement('text', 'coords', [
            'label' => $this->_tlabel . 'coords',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['coords'],
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);
        $fields['href'] = $this->createElement('text', 'href', [
            'label' => $this->_tlabel . 'href',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['href'],
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);
        $descriptionHttp = $this->getView()->translate($this->_tlabel . 'desc-http');
        $fields['href']->setDescription($descriptionHttp);

        $fields['target'] = $this->createElement('checkbox', 'target', [
            'label' => $this->_tlabel . 'target-blank',
            'value' => $this->_data['target'],
            'checkedValue' => '_blank',
            'uncheckedValue' => '',
        ]);

        $this->addDisplayGroup($fields, 'banner-' . $this->_key, [
            //'legend' => $this->_tlabel.$this->_key,
            'legend' => 'Banner ' . $this->_key,
            'decorators' => [
                'GroupDelete',
                'FormElements',
                [['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'group-wrapper group-main']],
            ],
        ]);

        $this->setDecorators([
            'FormElements',
        ]);
    }

    protected function setData($data)
    {
        $this->_data = array_merge(
            ['title' => '', 'alt' => '', 'coords' => '', 'href' => '', 'target' => ''],
            $data
        );
    }

    protected function setKey($key)
    {
        $this->_key = $key;
    }
}
