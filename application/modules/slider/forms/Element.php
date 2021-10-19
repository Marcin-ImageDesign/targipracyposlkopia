<?php

class Slider_Form_Element extends Engine_Form
{
    public function __construct($slider, $options = null)
    {
        parent::__construct($options);

        $this->setAttrib('ENCTYPE', 'multipart/form-data');
        //	$vAlreadyTaken = new Engine_Validate_AlreadyTaken();

        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'filters' => ['StringTrim'],
        ]);

        $translate = new Zend_View_Helper_Translate();
        $title->setDescription($translate->translate('Title'));

        $url = $this->createElement('text', 'url', [
            'label' => 'Link',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'field-text',
            'filters' => ['StringTrim', 'Link'],
        ]);

        $description = $this->createElement('tinyMce', 'description', [
            'label' => 'Content of the picture',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
        ]);
        $decorator = $description->getDecorator('data');
        $decorator->setOption('style', 'width: 600px;');

        $img = $this->createElement('File', 'img', [
            'label' => 'Photo',
            'decorators' => $this->elementDecoratorsFileImage,
            'allowEmpty' => false,
            'class' => 'field-text',
        ]);

        if ($slider->imageExists()) {
            $fileImageDecorator = $img->getDecorator('FileImage');
            $fileImageDecorator->setOptions([
                'image' => $slider->getBrowseImage(),
                'max-width' => 400,
                'max-height' => 200,
                'delete' => [
                    'route' => 'admin_slider_img_delete',
                    'params' => ['hash' => $slider->getHash()],
                    'onClickConfirm' => 'Are you sure you want to delete this image?',
                ],
            ]);
        }

        $img->getDecorator('row')->setOption('class', 'form-item img_content');

        $img->addValidator('Extension', false, ALLOWED_IMAGE_EXTENSIONS);
        $img->setDescription(ALLOWED_IMAGE_EXTENSIONS);
        $img->addValidator('Count', false, 1);

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
        $main->addDisplayGroup([$url, $description, $img], 'content');

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
