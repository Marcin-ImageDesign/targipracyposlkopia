<?php

class Brand_Form_Element extends Engine_Form
{
    protected $_belong_to = 'BrandFormElement';

    protected $_tlabel = 'form_brand-admin-element_';

    /**
     * @var Brand
     */
    protected $_model;

    /**
     * @var Doctrine_Collection
     */
    private $_brandList;

    public function init()
    {
        $name = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => true,
            'filters' => ['StringTrim'],
            'allowEmpty' => false,
            'value' => $this->_model->getName(),
        ]);

        $brandOptions = ['' => ''];
        $this->_brandList = Doctrine_Query::create()
            ->from('Brand b INDEXBY b.id_brand')
            ->where('b.id_brand_parent IS NULL')
            ->execute()
        ;

        foreach ($this->_brandList as $brand) {/** @var Brand $brand */
            if ($this->_model->getId() !== $brand->getId()) {
                $brandOptions[$brand->getId()] = $brand->getNameWithDashes();
            }
        }

        $parent = $this->createElement('select', 'parent', [
            'label' => $this->_tlabel . 'parent',
            'required' => false,
            'allowEmpty' => true,
            'multiOptions' => $brandOptions,
            'validators' => [
                ['InArray', false, [array_keys($brandOptions)]],
            ],
            'value' => $this->_model->id_brand_parent,
        ]);

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];
        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is_active',
            'multiOptions' => $this->translate($listOptionsNoYes),
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => $this->_model->is_active,
            'validators' => [
                ['InArray', false, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
        ]);

        $this->addDisplayGroup([$name], 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup([$parent], 'main');
        $this->addDisplayGroup([$is_active], 'aside', [
            'legend' => $this->_tlabel . 'group_attribs',
        ]);
    }

    protected function postIsValid($data)
    {
        $parentBrand = $this->getValue('parent');
        $this->_model->BrandParent = $parentBrand ? $this->_brandList[$this->getValue('parent')] : null;

        return parent::postIsValid($data);
    }
}
