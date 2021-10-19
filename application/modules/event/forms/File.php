<?php

class Event_Form_File extends Engine_FormAdmin
{
    /**
     * @var EventFile
     */
    private $eventFile;

    /**
     * @param EventFile $eventFile
     * @param array     $options
     */
    public function __construct($eventFile, $options = null)
    {
        parent::__construct($options);
        $this->eventFile = $eventFile;

        $this->setAttrib('ENCTYPE', 'multipart/form-data');
        $vStringLength = new Zend_Validate_StringLength(['max' => 255]);

        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'validators' => [$vStringLength],
            'value' => $this->eventFile->getTitle(),
            'filters' => ['StringTrim'],
        ]);

        $file = $this->createElement('file', 'file', [
            'label' => 'File',
            'decorators' => $this->elementDecoratorsCenturionFile,
            'allowEmpty' => true,
            'class' => 'field-file',
            'size' => 50,
        ]);
        $file->addValidator('Count', false, 1);

        if ($this->eventFile->isNew()) {
            $file->setRequired();
        } else {
            $file->setDescription($this->translate('Choose new file to exchange.'));
            $fileShowDecorator = $file->getDecorator('FileShow');
            $fileShowDecorator->setOptions([
                'title' => 'pokaż plik',
                'file' => $this->eventFile->getBrowserFile(),
            ]);
        }

        $image = $this->createElement('file', 'image', [
            'label' => 'Image',
            'allowEmpty' => true,
            'decorators' => $this->elementDecoratorsFileImage,
            'class' => 'field-file',
            'size' => 50,
        ]);
        $image->addValidator('Extension', false, ALLOWED_IMAGE_EXTENSIONS);
        $image->setDescription(ALLOWED_IMAGE_EXTENSIONS);
        $image->addValidator('Count', false, 1);

        if ($this->eventFile->isNew()) {
            $image->setRequired();
        } else {
            $image->setDescription(ALLOWED_IMAGE_EXTENSIONS . '. Wybierz nowy plik, aby podmienić.');
            $fileImageDecorator = $image->getDecorator('FileImage');
            $fileImageDecorator->setOptions([
                'image' => $this->eventFile->getBrowserImage(),
            ]);
        }

        $lead = $this->createElement('textarea', 'lead', [
            'label' => 'Description',
            'required' => false,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => true,
            'class' => ' field-text',
            'validators' => [],
            'value' => $this->eventFile->getLead(),
            'filters' => ['StringTrim'],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Zapisz',
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

        // nagłówek
        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$title, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        // cześć głowna
        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$file, $image, $lead], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->setLegend('Details');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        // dodanie subformularzu do formularza
        $this->addSubForm($header, 'header');
        $this->addSubForm($main, 'main');
    }
}
