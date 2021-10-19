<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marek Skotarek
 * Date: 26.06.13
 * Time: 09:53
 * To change this template use File | Settings | File Templates.
 */
class Event_Form_Admin_Sponsors_Banner extends Engine_Form
{
    // Variables
    protected $_tlabel = 'label_form_sponsors-banner_';
    protected $_data;
    protected $_key;

    // init
    public function init()
    {
        $fields = [];

        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['name'],
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $fields['link'] = $this->createElement('text', 'link', [
            'label' => $this->_tlabel . 'link',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_data['link'],
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $fields['target'] = $this->createElement('checkbox', 'target', [
            'label' => $this->_tlabel . 'target-blank',
            'value' => $this->_data['target'],
            'checkedValue' => '_blank',
            'uncheckedValue' => '',
        ]);

        $fields['image' . $this->_key] = $this->createElement('FileImage', 'image' . $this->_key, [
            'label' => $this->_tlabel . 'image',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
        ]);

        if (!empty($this->_data['image' . $this->_key])) {
            $imageDecorator = $fields['image' . $this->_key]->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'id_image' => $this->_data['image' . $this->_key],
                'crop' => true,
            ]);
        }

//        $fields['order'] = $this->createElement('text', 'order', array(
//            'label' => $this->_tlabel.'order',
        ////            'required' => true,
//            'allowEmpty' => true,
//            'value' => $this->_order,
//            'filters' => array('StringTrim'),
//            'validators' => array(
//                array('NotEmpty', true),
//            )
//        ));

        $fields['style'] = $this->createElement('text', 'style', [
            'label' => $this->_tlabel . 'style',
            'value' => $this->_data['style'],
            'filters' => ['StringTrim'],
        ]);

        $fields['image'] = $this->createElement('hidden', 'image', [
            'value' => $this->_data['image'],
        ]);

        $this->addDisplayGroup($fields, 'banner-' . $this->_key, [
            'legend' => $this->_tlabel . 'banner',
            'decorators' => [
                'GroupDelete',
                'FormElements',
                [['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'group-wrapper group-main sortSponsors']],
            ],
        ]);

        $this->setDecorators([
            'FormElements',
        ]);
    }

    // setters
    protected function setKey($key)
    {
        $this->_key = $key;
    }

    protected function setData($data)
    {
        $this->_data = $data;
    }
}
