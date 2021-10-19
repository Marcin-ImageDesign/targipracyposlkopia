<?php

/**
 * Description of Upload.
 *
 * @author marcin
 */
class Translation_Form_Upload extends Engine_FormAdmin
{
    public function __construct($options = null)
    {
        $languages = null;
        parent::__construct($options);
        $this->setAttrib('ENCTYPE', 'multipart/form-data');

//        $languages = array(''=>'');
        $baseUser = Zend_Registry::get('BaseUser');
        if ($baseUser instanceof BaseUser) {
            $langs = $baseUser->getBaseUserLanguage();
            foreach ($langs as $lang) {
                if ($lang instanceof Language) {
                    $languages[$lang->getId()] = $lang->getName();
                }
            }
        }

        $language = $this->createElement('select', 'language', [
            'label' => 'Language',
            'class' => 'field-text field-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'filters' => ['StringTrim'],
            'multiOptions' => $languages,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($languages)], ],
            ],
        ]);

        $file = $this->createElement('file', 'file', [
            'label' => 'CSV File',
            'decorators' => $this->elementDecoratorsCenturionFile,
            'allowEmpty' => true,
            'class' => 'field-file field-text-small',
            'size' => 50,
        ]);
        $file->addValidator('Extension', false, 'csv');
        $file->addValidator('Count', false, 1);

//        $file->setAttrib('style', 'width: 315px;');
        $file->setDescription($this->translate('Choose new file to exchange.'));
        $fileShowDecorator = $file->getDecorator('FileShow');
        $fileShowDecorator->setOptions([
            'title' => 'pokaż plik',
            //                'style'=>'width: 250px;'
        ]);

        $fileLabel = $file->getDecorator('Label');
        $fileLabel->setOption('style', 'width: 100%');

        $fileDataDecorator = $file->getDecorator('Data');
        $fileDataDecorator->setOption('style', 'width: 100%;');

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            //            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);
        $submit->addDecoratorHeader();

        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$language, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$file], 'content');

        $groupMain = $main->getDisplayGroup('content');
        $groupMain->clearDecorators();
        $groupMain->setLegend($this->translate('New file'));
        $groupMain->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);
        $main->addAttribs(['class' => 'form-main']);
        $this->addSubForm($header, 'header');
        $this->addSubForm($main, 'main');
    }
}
