<?php
/**
 * Description of Dictionary.
 *
 * @author Robert Rogiński <rroginski@voxoft.pl>
 */
class Admin_Form_Meta extends Engine_Form
{
    protected $_belong_to = 'Admin_Form_Meta';

    protected $_tlabel = 'form_meta-admin_';

    /**
     * @var Engine_Doctrine_Record
     */
    protected $_model;

    public function init()
    {
        $fields = [];

        $fields['meta_title'] = $this->createElement('text', 'meta_title', [
            'label' => $this->_tlabel . 'meta_title',
            'filters' => ['StringTrim'],
            'value' => $this->_model->getMetaTitle(),
        ]);

        $fields['meta_keywords'] = $this->createElement('text', 'meta_keywords', [
            'label' => $this->_tlabel . 'meta_keywords',
            'filters' => ['StringTrim'],
            'value' => $this->_model->getMetaKeywords(),
        ]);

        $fields['meta_description'] = $this->createElement('text', 'meta_description', [
            'label' => $this->_tlabel . 'meta_description',
            'filters' => ['StringTrim'],
            'value' => $this->_model->getMetaDescription(),
        ]);

        $this->addDisplayGroup($fields, 'main', [
            'legend' => $this->_tlabel . 'group-meta',
        ]);

        $this->setDecorators([
            'FormElements',
        ]);
    }
}
