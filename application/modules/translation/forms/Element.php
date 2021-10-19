<?php

/**
 * Description of Element.
 *
 * @author marcin
 */
class Translation_Form_Element extends Engine_FormAdmin
{
    /**
     * @var Translation
     */
    private $translation;

    public function __construct($translation, $options = null)
    {
        $this->translation = $translation;
        parent::__construct($options);
//        $params[] = array('id_language', '=', $this->translation->getLanguageId());
        $params = [];

        if (!$this->translation->isNew()) {
            $params[] = ['id_translation', '!=', $this->translation->getId()];
        }

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('Translation', 'text', $params);

        $text = $this->createElement('text', 'text', [
            'label' => 'Translated text',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'value' => $this->translation->getText(),
            'filters' => ['StringTrim'],
            'validators' => [$vAlreadyTaken],
        ]);

        $text->setDescription('Translated text');

        $translation = $this->createElement('text', 'translation', [
            'label' => 'Translation',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'value' => $this->translation->getTranslationText(),
            'filters' => ['StringTrim'],
        ]);

        $translation->setDescription('Translation');

        $languages = [];
        $baseUser = Zend_Registry::get('BaseUser');

        if ($baseUser instanceof BaseUser) {
            $langs = $baseUser->getBaseUserLanguage();
            foreach ($langs as $lang) {
                if ($lang instanceof Language) {
                    $languages[$lang->getId()] = $lang->getOriginalName();
                }
            }
        }

        $language = $this->createElement('select', 'language', [
            'label' => 'Language',
            'class' => 'field-text',
            'filters' => ['StringTrim'],
            'value' => $this->translation->getLanguageId(),
            'multiOptions' => $languages,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($languages)], ],
            ],
        ]);

        $options = [
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Visible',
            'multiOptions' => $listOptions,
            //            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => $this->translation->is_active,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptions)], ],
            ],
        ]);

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
        $header->addElements([$submit]);
        $header->addAttribs(['class' => 'form-header']);

        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$text, $translation, $language, $is_active], 'content');

        $groupMain = $main->getDisplayGroup('content');
        $groupMain->clearDecorators();
        $groupMain->setLegend($this->translate('Translation information'));
        $groupMain->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);
        $main->addAttribs(['class' => 'form-main']);
        $this->addSubForm($header, 'header');
        $this->addSubForm($main, 'main');
    }

    public function isValid($data)
    {
        $vAlreadyTaken = $this->main->text->getValidator('AlreadyTaken');
        $params = $vAlreadyTaken->getParams();
        $params[] = ['id_language', '=', $data['main']['language']];
        $vAlreadyTaken->setParams($params);

        return parent::isValid($data);
    }
}
