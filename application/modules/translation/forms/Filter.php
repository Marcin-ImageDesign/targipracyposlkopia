<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Filter.
 *
 * @author marcin
 */
class Translation_Form_Filter extends Engine_FormAdmin
{
    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $text = $this->createElement('text', 'text', [
            'label' => 'Translated text',
            'class' => 'field-text',
            'filters' => ['StringTrim'],
            'allowEmpty' => true,
            'decorators' => $this->elementDecoratorsCenturion,
        ]);

        $translation = $this->createElement('text', 'translation', [
            'label' => 'Translation',
            'class' => 'field-text',
            'filters' => ['StringTrim'],
            'allowEmpty' => true,
            'decorators' => $this->elementDecoratorsCenturion,
        ]);

        $languages = ['' => ''];
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
            'multiOptions' => $languages,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($languages)], ],
            ],
        ]);

        $date_start = $this->createElement('DatePicker', 'date_start', [
            'label' => 'Date start',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
        ]);

        $date_end = $this->createElement('DatePicker', 'date_end', [
            'label' => 'Date end',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'validators' => [new Zend_Validate_Date(['format' => 'YYYY-MM-dd'])],
        ]);

        $options = [
            '0' => 'No',
            '1' => 'Yes',
        ];
        $listOptions = $this->translate($options);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Is active',
            'class' => 'select-filter',
            // 'decorators'=>$this->elementDecorators,
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'item-class' => 'short_text-inline',
            'validators' => [['InArray',
                false,
                [array_keys($listOptions)],
            ]],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Search',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => 'Clear',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->addDisplayGroup([$text, $translation, $language, $date_start, $date_end, $is_active, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }

    public function populate(array $values)
    {
        $date_start = $values['date_start'];
        $date_end = $values['date_end'];
        if ((strtotime($date_start) > strtotime($date_end)) && (!empty($date_start) && !empty($date_end))) {
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }
        $values['date_start'] = $date_start;
        $values['date_end'] = $date_end;

        return parent::populate($values);
    }
}
