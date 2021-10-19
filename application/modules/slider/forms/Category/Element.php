<?php

class Slider_Form_Category_Element extends Engine_Form
{
    public function __construct($sliderCategory, $options = null)
    {
        $params = null;
        parent::__construct($options);

        //sprawdzenie czy mamy doczynienia z nowym elementem czy edycją już istniejącego
        if (false === $sliderCategory->isNew()) {
            $params[] = ['id_slider_category', '!=', $sliderCategory->getId()];
        }

        //ustawienie zmiennych odpowiedzilanych za język
        $model = 'SliderCategory';
        $params[] = ['id_base_user', '=', $sliderCategory->BaseUser->getId()];

        //budowa validatora uniemożliwiającego zapisanie slidera o tytule który już istnieje
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken($model, 'uri', $params);

        $this->setAttrib('ENCTYPE', 'multipart/form-data');

        $vOnlyNumber = new Zend_Validate_Digits();
        //	$vOnlyNumber->setMessage('Może zawierać jedynie liczby.');

        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);
        $translate = new Zend_View_Helper_Translate();
        $title->setDescription($translate->translate('Title'));

        $width = $this->createElement('text', 'width', [
            'label' => 'Width',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'validators' => [$vOnlyNumber],
        ]);

        $height = $this->createElement('text', 'height', [
            'label' => 'Height',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'validators' => [$vOnlyNumber],
        ]);
        $uri = $this->createElement('text', 'uri', [
            'label' => 'ID',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'validators' => [$vAlreadyTaken],
        ]);

        $listOptions = [
            '0' => 'Nie',
            '1' => 'Tak',
        ];

        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Is active',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptions)], ],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->setDecorators([
            'Description',
            'FormElements',
            'Form',
        ]);

        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$title, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$width, $height, $uri], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->setLegend('Content');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        $aside = new Zend_Form_SubForm();
        $aside->setDisableLoadDefaultDecorators(false);
        $aside->setDecorators($this->subFormDecoratorsCenturion);
        $aside->addDisplayGroup([$is_active], 'aside');

        $groupAside = $aside->getDisplayGroup('aside');
        $groupAside->clearDecorators();
        $groupAside->setLegend($translate->translate('Options'));
        $groupAside->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $aside->addAttribs(['class' => 'form-aside']);

        $this->addSubForm($header, 'header');
        $this->addSubForm($main, 'main');
        $this->addSubForm($aside, 'aside');
    }
}
