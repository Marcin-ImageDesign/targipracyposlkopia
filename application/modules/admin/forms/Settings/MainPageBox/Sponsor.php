<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marek Skotarek
 * Date: 01.07.13
 * Time: 13:53
 * To change this template use File | Settings | File Templates.
 */
class Admin_Form_Settings_MainPageBox_Sponsor extends Engine_Form
{
    protected $_subFormAttributesA = [];
    protected $_subFormHtmlWrap = [];
    protected $_subFormAttributes = [];

    protected $_belong_to = 'AdminFormSettingsMainPageBoxSponsor';

    protected $_tlabel = 'form_admin_form_settings_main_page_box_sponsor_';

    protected $_data;
    protected $_key;

    public function init()
    {
        $fields = [];

        $fields['title'] = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_data['title'],
        ]);

        $fields['link'] = $this->createElement('text', 'link', [
            'label' => $this->_tlabel . 'link',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_data['link'],
        ]);
        $description = $this->getView()->translate('label_form_settings_http_required');
        $fields['link']->setDescription($description);

        $fields['class'] = $this->createElement('text', 'class', [
            'label' => $this->_tlabel . 'class',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_data['class'],
        ]);

        $fields['target'] = $this->createElement('checkbox', 'target', [
            'label' => $this->_tlabel . 'target-blank',
            'value' => $this->_data['target'],
            'uncheckedValue' => '',
            'checkedValue' => '_blank',
        ]);

        $fields['html'] = $this->createElement('text', 'html', [
            'label' => $this->_tlabel . 'html',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_data['html'],
        ]);

        $fields['image' . $this->_key] = $this->createElement('FileImage', 'image' . $this->_key, [
            'label' => $this->_tlabel . 'image',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
        ]);

        $fields['img_width'] = $this->createElement('text', 'img_width', [
            'label' => $this->_tlabel . 'width',
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_data['img_width'],
        ]);

        $fields['img_height'] = $this->createElement('text', 'img_height', [
            'label' => $this->_tlabel . 'height',
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_data['img_height'],
        ]);

        if (!empty($this->_data['image' . $this->_key])) {
            $imageDecorator = $fields['image' . $this->_key]->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'id_image' => $this->_data['image' . $this->_key],
                'crop' => true,
            ]);
            if (empty($this->_data['img_width'])) {
                $fields['img_width']->setRequired(true);
            }
            if (empty($this->_data['img_height'])) {
                $fields['img_height']->setRequired(true);
            }
        }

        $this->addElements($fields);

        $this->addAttributesA();
        $this->addAttributes();
        $this->addHtmlWrap();

        $this->setDecorators([
            'FormElements',
        ]);
    }

    public function addAttributesA()
    {
        $this->_subFormAttributesA = new Admin_Form_Settings_MainPageBox_Sponsor_AttributesA([
            'data' => $this->_data['attr_a'],
        ]);
        $this->_subFormAttributesA->setElementsBelongTo('attr_a');
        $this->addSubForm($this->_subFormAttributesA, 'banner-sub');
    }

    public function addAttributes()
    {
        $this->_subFormAttributes = new Admin_Form_Settings_MainPageBox_Sponsor_Attributes([
            'data' => $this->_data['attr'],
        ]);

        $this->_subFormAttributes->setElementsBelongTo('attr');
        $this->addSubForm($this->_subFormAttributes, 'banner-sub-attributes');
    }

    public function addHtmlWrap()
    {
        $this->_subFormHtmlWrap = new Admin_Form_Settings_MainPageBox_Sponsor_HtmlWrap([
            'data' => $this->_data['html_wrap'],
        ]);

        $this->_subFormHtmlWrap->setElementsBelongTo('html_wrap');
        $this->addSubForm($this->_subFormHtmlWrap, 'banner-sub-html');
    }

    protected function setData($data)
    {
        $this->_data = $data;
    }

    protected function setKey($key)
    {
        $this->_key = $key;
    }
}
