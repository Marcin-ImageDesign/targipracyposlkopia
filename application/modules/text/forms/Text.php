<?php
/**
 * Description of Dictionary.
 *
 * @author Robert Rogiński <rroginski@voxoft.pl>
 */
class Text_Form_Text extends Engine_Form
{
    protected $_belong_to = 'Text_Form_Text';

    protected $_tlabel = 'form_text-admin_';

    /**
     * @var Text
     */
    protected $_model;

    public function init()
    {
        $header = [];
        $fields = [];
        $aside = [];

        $header['title'] = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getTitle(),
        ]);

        $vAlreadyTakenParams = [['id_language', '=', Engine_I18n::getLangId()]];
        if (!$this->_model->isNew()) {
            $vAlreadyTakenParams[] = ['id_text', '!=', $this->_model->getId()];
        }

        $fields['uri'] = $this->createElement('text', 'uri', [
            'label' => $this->_tlabel . 'uri',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getUri(),
            'validators' => [
                ['NotEmpty', true],
                ['Regex', true, ['pattern' => '/^[(a-zA-Z0-9-_)]+$/']],
                ['AlreadyTaken', true, ['TextTranslation', 'uri', $vAlreadyTakenParams]],
            ],
        ]);

        $fields['text'] = $this->createElement('wysiwyg', 'text', [
            'label' => $this->_tlabel . 'text',
            'required' => true,
            'allowEmpty' => false,
            'editor' => 'full',
            'wrapper-class' => 'full',
            'filters' => ['StringTrim'],
            'value' => $this->_model->getText(),
        ]);

        $aside['is_active'] = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $this->_listOptionsNoYes,
            'allowEmpty' => false,
            'required' => true,
            'class' => 'field-switcher',
            'value' => (int) $this->_model->getIsActive(),
            'validators' => [
                ['InArray', true, [array_keys($this->_listOptionsNoYes)]],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $metaSubForm = new Admin_Form_Meta(['model' => $this->_model]);

        $this->addDisplayGroup($header, 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup($aside, 'aside', [
            'legend' => $this->_tlabel . 'group_aside',
        ]);
        $this->addDisplayGroup($fields, 'main', [
            'legend' => $this->_tlabel . 'group-main',
        ]);

        $this->addSubForm($metaSubForm, 'meta');
    }
}
