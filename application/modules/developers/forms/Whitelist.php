<?php

class Developers_Form_Whitelist extends Engine_Form
{
    /**
     * @var News
     */
    protected $_model;

    protected $_belong_to = 'DevelopersFormsWhitelist';

    protected $_tlabel = 'form_developers_admin_whitelist_';

    public function init()
    {
        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');

        $address = $this->createElement('text', 'address', [
            'label' => $this->_tlabel . 'address',
            'required' => true,
            'allowEmpty' => false,
            'validators' => [],
            'value' => $this->_model->getAddress(),
            'filters' => ['StringTrim'],
        ]);

        $description = $this->createElement('text', 'description', [
            'label' => $this->_tlabel . 'description',
            'required' => false,
            'allowEmpty' => true,
            'validators' => [],
            'value' => $this->_model->getDescription(),
            'filters' => ['StringTrim'],
        ]);

        $this->addDisplayGroup(
            [$address, $description],
            'main',
            [
                'class' => 'group-wrapper group-main',
                //'legend' => 'IP'
            ]
        );
    }

    protected function setModel($model)
    {
        $this->_model = $model;
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_model->setAddress($data['address']);
        $this->_model->setDescription($data['description']);

        return true;
    }
}
