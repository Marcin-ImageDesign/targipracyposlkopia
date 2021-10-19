<?php
/**
 * Description of Email.
 *
 * @author marcin
 */
class Form_RecoveryEmail extends Engine_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setAttribs(['class' => 'form ajaxForm', 'autocomplete' => 'off', 'id' => 'RecoveryPass']);

        $vEmail = new Zend_Validate_EmailAddress();
        $vEmpty = new Zend_Validate_NotEmpty();
        $vRecordExists = new Engine_Validate_RecordExists('User', 'email');

        $hashSecurity = new Zend_Form_Element_Hash('hash_security', [
        ]);

        $this->addElement($hashSecurity);

        $email = $this->createElement('text', 'email', [
            'Label' => 'Email',
            'required' => true,
            'class' => 'text',
            'filters' => ['StringTrim'],
        ]);

        $email->addValidator($vEmail, true);
        $email->addValidator($vEmpty, true);
        $email->addValidator($vRecordExists, true);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Send',
            'ignore' => true,
        ]);

        $this->addDisplayGroup(
            [$email, $submit],
            'main'
        );
    }
}
