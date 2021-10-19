<?php

class Form_ChangePass extends Engine_Form
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User  $user
     * @param array $options
     */
    public function __construct($user, $options = null)
    {
        parent::__construct($options);
        $this->user = $user;

        $notEmpty = new Zend_Validate_NotEmpty();
        $vStringLength = new Zend_Validate_StringLength(['min' => 6, 'max' => 20]);
        $vIdentival = new Zend_Validate_Identical('password');

        $hashSecurity = new Zend_Form_Element_Hash('hash_security', [
        ]);

        $password = $this->createElement('password', 'password', [
            'label' => 'Password',
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $notEmpty],
            'class' => 'text',
        ]);

        $password_repeat = $this->createElement('password', 'password_repeat', [
            'label' => 'Repeat password',
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $vIdentival, $notEmpty],
            'class' => 'text',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Wyślij',
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $this->addDisplayGroup([$hashSecurity, $password, $password_repeat, $submit], 'main');
    }

    public function isValid($data)
    {
        return parent::isValid($data);
    }
}
