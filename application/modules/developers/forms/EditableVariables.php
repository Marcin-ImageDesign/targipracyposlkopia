<?php

/**
 * @author Piotr Wasilewski <pwasilewski@voxoft.com>
 */
class Developers_Form_EditableVariables extends Engine_Form
{
    protected $_belong_to = 'DevelopersFormEditableVariables';

    protected $_tlabel = 'form_developers-editable_variables_';

    protected $_variablesList = [];

    protected $_fields;

    protected $_group;

    public function init()
    {
        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'send',
        ]);
        $this->addDisplayGroup([$submit], 'buttons');

        $this->_addFormFields();
    }

    protected function setVariablesList(array $variablesList)
    {
        $this->_variablesList = $variablesList;
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        // usunięcie klucza z submit
        unset($data['submit']);

        // zapis zmiennych do bazy
        foreach ($data as $key => $value) {
            Variable::setVariable($key, $value);
        }

        return true;
    }

    private function _addFormFields()
    {
        // przeglądanie listy wg grup zmiennych
        foreach ($this->_variablesList as $variableGroupName => $variables) {
            // dodajemy odpowiednie pole w formularzu dla każdej zmiennej
            $fieldsInGroup = [];
            foreach ($variables as $variable) {
                $fieldsInGroup[] = $this->_addFormField($variable);
            }

            // tworzenie DisplayGriup dla każdej grupy zmiennyc
            $this->_addFieldsGroup($variableGroupName, $fieldsInGroup);
        }
    }

    private function _addFormField($variable)
    {
        $field = $this->createElement($variable['type'], $variable['name'], [
            'label' => $this->_tlabel . $variable['name'],
            'required' => (isset($variable['required'])) ? $variable['required'] : false,
            'allowEmpty' => (isset($variable['allowEmpty'])) ? $variable['allowEmpty'] : true,
            'validators' => (isset($variable['validators'])) ? $variable['validators'] : [],
        ]);

        // w przypadku select'ów
        if (isset($variable['multiOptions'])) {
            $field->setMultiOptions($variable['multiOptions']);
        }

        return $field;
    }

    private function _addFieldsGroup($groupName, $fieldsInGroup)
    {
        $this->addDisplayGroup(
            $fieldsInGroup,
            $groupName,
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . $groupName,
            ]
        );
    }
}
