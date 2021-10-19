<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marek Skotarek
 * Date: 02.07.13
 * Time: 10:04
 * To change this template use File | Settings | File Templates.
 */
class Admin_Form_Settings_MainPageBox_Sponsor_HtmlWrap_Attr extends Engine_Form
{
    protected $_belong_to = 'AdminFormSettingsMainPageBoxSponsorHtmlWrapAttr';
    protected $_tlabel = 'form_admin_form_settings_main_page_box_sponsor_html_wrap_attr_';

    protected $_data;

    public function init()
    {
        $fields = [];

        $fields['class'] = $this->createElement('text', 'class', [
            'label' => $this->_tlabel . 'class',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_data['class'],
        ]);

        $fields['style'] = $this->createElement('text', 'style', [
            'label' => $this->_tlabel . 'style',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_data['style'],
        ]);

        $this->addElements($fields);

        $this->setDecorators(['FormElements']);
    }

    protected function setData($data)
    {
        $this->_data = array_merge(
            ['class' => '', 'style' => ''],
            $data
        );
    }
}
