<?php

class Admin_Form_Settings extends Engine_FormAdmin
{
    private $_model;

    public function init()
    {
        $fileDescriptionSize = $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB';

        $image = $this->createElement('FileImage', 'image', [
            'label' => $this->_tlabel . 'Event logo',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        if (!empty($this->_model->id_image)) {
            $imageDecorator = $image->getDecorator('FileImage');
            $imageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->_model->id_image),
            ]);
        }
        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        $use_ssl = $this->createElement('select', 'use_ssl', [
            'label' => 'SSL',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => Engine_Variable::getInstance()->getVariable(Variable::USE_SSL),
            'validators' => [
                ['InArray', true, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $name = $this->createElement('text', 'name', [
            'label' => 'Name',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'filters' => ['StringTrim'],
        ]);

        $metatag_title = $this->createElement('text', 'metatag_title', [
            'label' => 'Title',
            'required' => false,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);

        $metatag_key = $this->createElement('text', 'metatag_key', [
            'label' => 'Keywords',
            'required' => false,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);

        $metatag_desc = $this->createElement('text', 'metatag_desc', [
            'label' => 'Description',
            'required' => false,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$name, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        $img = new Zend_Form_SubForm();
        $img->setDisableLoadDefaultDecorators(false);
        $img->setDecorators($this->subFormDecoratorsCenturion);
        $img->addDisplayGroup([$image], 'img');
        $groupImg = $img->getDisplayGroup('img');
        $groupImg->clearDecorators();
        $groupImg->setLegend('Event logo');
        $groupImg->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $img->addAttribs(['class' => 'form-main']);

        $seo = new Zend_Form_SubForm();
        $seo->setDisableLoadDefaultDecorators(false);
        $seo->setDecorators($this->subFormDecoratorsCenturion);
        $seo->addDisplayGroup([$metatag_title, $metatag_key, $metatag_desc], 'seo');
        $groupSeo = $seo->getDisplayGroup('seo');
        $groupSeo->clearDecorators();
        $groupSeo->setLegend('SEO');
        $groupSeo->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $seo->addAttribs(['class' => 'form-main']);

        $ssl = new Zend_Form_SubForm();
        $ssl->setDisableLoadDefaultDecorators(false);
        $ssl->setDecorators($this->subFormDecoratorsCenturion);
        $ssl->addDisplayGroup([$use_ssl], 'ssl');
        $groupSsl = $ssl->getDisplayGroup('ssl');
        $groupSsl->clearDecorators();
        $groupSsl->setLegend('SSL');
        $groupSsl->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $ssl->addAttribs(['class' => 'form-main']);

        $this->addSubForm($header, 'header');
        $this->addSubForm($ssl, 'ssl');
        $this->addSubForm($img, 'img');
        $this->addSubForm($seo, 'seo');
    }

    public function populate(array $values)
    {
        // jeśli edytujemy, nie sprawdzamy samego siebie
        // if( !empty( $values['id_base_user'] ) ){
        //     $vAlreadyTakenDomain = $this->getSubForm('main')->getElement('domain')->getValidator('AlreadyTaken');
        //     $vAlreadyTakenDomain->mergeParams( array( array( 'id_base_user', '!=', $values['id_base_user'] ) ) );

        //     $vAlreadyTakenSubdomain = $this->getSubForm('main')->getElement('subdomain')->getValidator('AlreadyTaken');
        //     $vAlreadyTakenSubdomain->mergeParams( array( array( 'id_base_user', '!=', $values['id_base_user'] ) ) );

        //     $vAlreadyTakenDirectory = $this->getSubForm('main')->getElement('directory')->getValidator('AlreadyTaken');
        //     $vAlreadyTakenDirectory->mergeParams( array( array( 'id_base_user', '!=', $values['id_base_user'] ) ) );
        // }

        parent::populate($values);
    }

    protected function setModel($model)
    {
        $this->_model = $model;
    }
}
