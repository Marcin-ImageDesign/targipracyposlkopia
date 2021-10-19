<?php

/**
 * Description of File.
 *
 * @author Marek Skotarek
 */
class Event_Form_StandFile extends Engine_FormAdmin
{
    /**
     * @var ExhibStandFile
     */
    protected $exhib_stand_file;

    /**
     * @var BaseUser
     */
    protected $baseUser;

    /**
     * @param ExhibStandFile $exhib_stand_file
     * @param array $options
     */
    public function __construct($exhib_stand_file, $options = null)
    {
        parent::__construct($options);

        $vStringLength = new Zend_Validate_StringLength(['min' => 1, 'max' => 255]);
        $this->baseUser = Zend_Registry::get('BaseUser');
        $this->exhib_stand_file = $exhib_stand_file;

        $notEmpty = new Zend_Validate_NotEmpty();
        $notEmpty->setMessage('This field is required');

        $vOnlyNumber = new Zend_Validate_Digits();
        $vOnlyNumber->setMessage('Field can contain onlu numbers');

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'cms-button_save',
            'type' => 'submit',
            'ignore' => true,
            'id' => 's',
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $listOptions = [
            '0' => 'Nie',
            '1' => 'Tak',
        ];

        $aside = [];
        $aside['isVisible'] = $this->createElement('select', 'is_visible', [
            'label' => 'Is visible',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int)$this->exhib_stand_file->is_visible,
            'validators' => [
                ['InArray', true, [array_keys($listOptions)]],
            ],
        ]);

        $publishedLimit = ExhibStandFile::$publishedLimit[$this->exhib_stand_file->ExhibStand->id_stand_level];
        if ($this->exhib_stand_file->is_published || $this->exhib_stand_file->ExhibStand->getPublishedFilesCount() < $publishedLimit) {
            $aside['isPublished'] = $this->createElement('select', 'is_published', [
                'label' => 'Is published',
                'multiOptions' => $listOptions,
                'allowEmpty' => false,
                'class' => 'field-switcher',
                'value' => (int)$this->exhib_stand_file->is_published,
                'validators' => [
                    ['InArray', true, [array_keys($listOptions)]],
                ],
            ]);
        }

        $name = $this->createElement('text', 'name', [
            'label' => 'stand_file Name',
            'required' => true,
            'class' => 'field-text field-text-big',
            'value' => $this->exhib_stand_file->name,
            'filters' => ['StringTrim'],
        ]);
        $name->addValidator($vStringLength)->addValidator($notEmpty);
        $name->setDescription('Max. długość 255 znaków.');

        $description = $this->createElement('tinyMce', 'description', [
            'label' => 'Description',
            'editorOptions' => ['height' => '30px'],
            'value' => $this->exhib_stand_file->getDescription(),
        ]);

        $decorator = $description->getDecorator('data');
        $decorator->setOption('style', 'width: 600px; margin-top: 10px;');

        $file = $this->createElement('file', 'file', [
            'label' => 'File',
            //            'decorators' => $this->elementDecoratorsCenturionFile,
            'allowEmpty' => true,
        ]);

        $file->addValidator('Extension', false, ALLOWED_FILE_EXTENSIONS);
        $file->addValidator('Count', false, 1);
        $file->addValidator('Size', false, MAX_FILE_SIZE);

        if (!$this->exhib_stand_file->isNew()) {
            $file->setDescription($this->translate('file_swap') . $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB');
            $file->setDecorators($this->elementDecoratorsCenturionFile);
            $fileShowDecorator = $file->getDecorator('FileShow');
            $fileShowDecorator->setOptions([
                'title' => 'download file ' . $this->exhib_stand_file->getBrowserFileName(),
                'file' => $this->getView()->url(['hash' => $this->exhib_stand_file->getHash()], 'event_admin-stand-files_download'),
            ]);
        } else {
            $file->setDescription(ALLOWED_FILE_EXTENSIONS . $this->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB');
        }

        $image = $this->createElement('file', 'image', [
            'label' => 'Image',
            'allowEmpty' => true,
            'decorators' => $this->elementDecoratorsFileImage,
            'class' => 'field-file',
            'size' => 50,
        ]);
        $image
            ->addValidator('Extension', false, ALLOWED_IMAGE_EXTENSIONS)
            ->setDescription(ALLOWED_IMAGE_EXTENSIONS)
            ->addValidator('Count', false, 1);

        if ($this->exhib_stand_file->isNew()) {
            $image->setRequired();
        } else {
            $image->setDescription(ALLOWED_IMAGE_EXTENSIONS . '. Wybierz nowy plik, aby podmienić.');
            $fileImageDecorator = $image->getDecorator('FileImage');
            $fileImageDecorator->setOptions([
                'image' => $this->exhib_stand_file->getBrowserImage(),
            ]);
        }

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);

        $main->addDisplayGroup([$name, $file, $image, $description], 'content');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup($aside, 'aside', ['class' => 'group-wrapper group-aside exClearRight']);
        $groupMain = $main->getDisplayGroup('content');
        $groupMain->clearDecorators();
        $groupMain->setLegend('Settings');
        $groupMain->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);
        $main->addAttribs(['class' => 'form-main']);
        $this->addSubForm($main, 'main');
    }

    public function populateModel()
    {
        $subForms = $this->getSubForms();
        foreach ($subForms as $subForm) {
            $populateMethod = 'populate' . ucfirst($subForm->getName());
            if (method_exists($this, $populateMethod)) {
                $this->{$populateMethod}();
            }
        }
    }

    public function isValid($data)
    {
        return parent::isValid($data);
    }
}
