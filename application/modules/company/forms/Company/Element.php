<?php

class Company_Form_Company_Element extends Engine_Form
{
    protected $_tlabel = 'form_filter_company_';

    /**
     * @var Company
     */
    protected $_model;

    public function init()
    {
        $vStringLength = new Zend_Validate_StringLength(['min' => 0, 'max' => 255]);
        $vNIPLength = new Zend_Validate_StringLength(['min' => 10, 'max' => 10]);
        $vRegonLength = new Zend_Validate_Callback(['callback' => function ($krs) {
            return $this->regonValid($krs);
        }]);
        $vRegonLength->setMessage("'%value%' must be 9 or 14 numbers");
        $filterReplace = new Zend_Filter_PregReplace([
            'match' => '/\-/',
            'replace' => '',
        ]);
        $filterSpace = new Zend_Filter_PregReplace([
            'match' => '/\s+/',
            'replace' => '',
        ]);
        $params = [[
            'id_base_user', '=', $this->_model->id_base_user,
        ]];
        if (!$this->_model->isNew()) {
            $params[] = ['id_company', '!=', $this->_model->getId()];
        }

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('Company', 'nip', $params);
        $vAlreadyTakenKrs = new Engine_Validate_AlreadyTaken('Company', 'krs', $params);
        $vAlreadyTakenRegon = new Engine_Validate_AlreadyTaken('Company', 'regon', $params);
        $vInt = new Zend_Validate_Regex(['pattern' => '/^(\d){1,}$/']);
        $vInt->setMessage("'%value%' is not a integer number", Zend_Validate_Regex::NOT_MATCH);

        $fields = [];
        $aside = [];

        $name = $this->createElement('text', 'name', [
            $this->_tlabel . 'name',
            'required' => true,
            'filters' => ['StringTrim'],
            'description' => $this->_tlabel . 'name-desc',
            'value' => $this->_model->getName(),
        ]);
        $name->addValidator($vStringLength);

        $fields['nip'] = $this->createElement('text', 'nip', [
            'label' => $this->_tlabel . 'nip',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getNip(),
        ]);
        $fields['nip']->addFilter($filterReplace);
        $fields['nip']->addFilter($filterSpace);
//        $nip->addValidator($vNIPLength);
        $fields['nip']->addValidator($vAlreadyTaken);
//        $nip->addValidator($vInt);

        $fields['www'] = $this->createElement('text', 'www', [
            'label' => $this->_tlabel . 'site',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getWww(),
        ]);

        $fields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getEmail(),
        ]);

        $fields['krs'] = $this->createElement('text', 'krs', [
            'label' => $this->_tlabel . 'krs',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getKrs(),
        ]);
        $fields['krs']->addFilter($filterSpace);
        $fields['krs']->addValidator($vAlreadyTakenKrs);
        $fields['krs']->addFilter($filterReplace);

        $fields['regon'] = $this->createElement('text', 'regon', [
            'label' => $this->_tlabel . 'regon',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getRegon(),
        ]);

        $fields['regon']->addFilter($filterReplace);
        $fields['regon']->addFilter($filterSpace);
        $fields['regon']->addValidator($vAlreadyTakenRegon);
        $fields['regon']->addValidator($vRegonLength);

        $fields['representative'] = $this->createElement('text', 'representative', [
            'label' => $this->_tlabel . 'representative',
            'required' => false,
            'filters' => ['StringTrim'],
            'value' => $this->_model->getRepresentative(),
        ]);

        $listOptions = ['0' => 'label_no', '1' => 'label_yes'];
        $aside['is_active'] = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'value' => (int) $this->_model->is_active,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray', false, [array_keys($listOptions)]],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'cms-label_save',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->addDisplayGroup([$name], 'header');
        $this->addDisplayGroup([$submit], 'buttons');
        $this->addDisplayGroup($fields, 'main', ['legend' => $this->_tlabel . '_legend-data']);
        $this->addDisplayGroup(
            $aside,
            'aside',
            [
                'legend' => $this->_tlabel . '_legend-options',
            ]
        );
    }

    public function populate($values)
    {
        parent::populate($values);
    }

    public function regonValid($krs)
    {
        return 9 === mb_strlen($krs) || 14 === mb_strlen($krs);
    }
}
