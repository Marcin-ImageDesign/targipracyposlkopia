<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marek Skotarek
 * Date: 02.07.13
 * Time: 09:24
 * To change this template use File | Settings | File Templates.
 */
class Admin_Form_Settings_MainPageBox_Sponsor_HtmlWrap extends Engine_Form
{
    protected $_subFormHtmlWrapAttr = [];

    protected $_belong_to = 'AdminFormSettingsMainPageBoxSponsorHtmlWrap';
    protected $_tlabel = 'form_admin_form_settings_main_page_box_sponsor_html_wrap_';

    protected $_data;

    public function init()
    {
        $fields = [];

        $fields['tag'] = $this->createElement('text', 'tag', [
            'label' => $this->_tlabel . 'tag',
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_data['tag'],
        ]);

        $this->addAttr();
        $this->addElements($fields);

        $this->setDecorators([
            'FormElements',
        ]);
    }

    public function addAttr()
    {
        $this->_subFormHtmlWrapAttr = new Admin_Form_Settings_MainPageBox_Sponsor_HtmlWrap_Attr([
            'data' => $this->_data['attr'],
        ]);

        $this->_subFormHtmlWrapAttr->setElementsBelongTo('attr');
        $this->addSubForm($this->_subFormHtmlWrapAttr, 'banner-sub-html-wrap-attr');
    }

    protected function setData($data)
    {
        $this->_data = array_merge(
            ['tag' => '', 'attr' => ['class' => '', 'style' => '']],
            $data
        );
    }
}
