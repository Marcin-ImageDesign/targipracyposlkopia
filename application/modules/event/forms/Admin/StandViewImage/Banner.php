<?php

class Event_Form_Admin_StandViewImage_Banner extends Engine_Form
{
    protected $_tlabel = 'label_form_stand-view-image-banner_';

    private $_data;
    private $_key;

    public function init()
    {
        $fields = [];

        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_key,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
                ['Regex', true, ['pattern' => '/^[(a-zA-Z0-9)]+$/']],
            ],
        ]);

        $fields['x'] = $this->createElement('text', 'x', [
            'label' => $this->_tlabel . 'pos_x',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['x'],
            'filters' => ['StringTrim', 'Int'],
            'validators' => [
                ['NotEmpty', true],
                ['Between', true, ['min' => 1, 'max' => 1000]],
            ],
        ]);

        $fields['y'] = $this->createElement('text', 'y', [
            'label' => $this->_tlabel . 'pos_y',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['y'],
            'filters' => ['StringTrim', 'Int'],
            'validators' => [
                ['NotEmpty', true],
                ['Between', true, ['min' => 1, 'max' => 1000]],
            ],
        ]);

        $fields['width'] = $this->createElement('text', 'width', [
            'label' => $this->_tlabel . 'width',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['width'],
            'filters' => ['StringTrim', 'Int'],
            'validators' => [
                ['NotEmpty', true],
                ['Between', true, ['min' => 1, 'max' => 1000]],
            ],
        ]);

        $fields['height'] = $this->createElement('text', 'height', [
            'label' => $this->_tlabel . 'height',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['height'],
            'filters' => ['StringTrim', 'Int'],
            'validators' => [
                ['NotEmpty', true],
                ['Between', true, ['min' => 1, 'max' => 1000]],
            ],
        ]);

        $fields['perspective'] = $this->createElement('text', 'perspective', [
            'label' => $this->_tlabel . 'perspective',
            'value' => $this->_data['perspective'],
            'filters' => ['StringTrim'],
        ]);

        $fields['crop'] = $this->createElement('text', 'crop', [
            'label' => $this->_tlabel . 'crop',
            'value' => $this->_data['crop'],
            'filters' => ['StringTrim'],
        ]);

        $fields['style'] = $this->createElement('text', 'style', [
            'label' => $this->_tlabel . 'style',
            'value' => $this->_data['style'],
            'filters' => ['StringTrim'],
        ]);

        $this->addDisplayGroup($fields, 'banner-' . $this->_key, [
            'legend' => $this->_key,
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

    /**
     * @param $data array
     */
    protected function setData($data)
    {
        $this->_data = array_merge(
            ['x' => '', 'y' => '', 'width' => '', 'height' => '', 'perspective' => '', 'crop' => '', 'style' => ''],
            $data
        );
    }

    /**
     * @param $key array
     */
    protected function setKey($key)
    {
        $this->_key = $key;
    }
}
