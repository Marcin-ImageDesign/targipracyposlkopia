<?php

class User_Form_ChangePass extends Engine_Form
{
    protected $_belong_to = 'UserChangePass';

    /**
     * @var User
     */
    private $_user;

    /**
     * @param User  $user
     * @param array $options
     */
    public function __construct($user, $options = null)
    {
        parent::__construct($options);

        // settings form
        $this->setAttribs(['class' => 'form ajaxForm', 'autocomplete' => 'off', 'id' => $this->getElementsBelongTo() . 'Form']);

        $this->_user = $user;

        $notEmpty = new Zend_Validate_NotEmpty();
        $vStringLength = new Zend_Validate_StringLength(['min' => 6, 'max' => 20]);
        $vIdentival = new Zend_Validate_Identical('password');

        $hashSecurity = new Zend_Form_Element_Hash('hash_security', [
            // 'decorators' => $this->buttonDecoratorsCenturion
        ]);

        $password_actual = $this->createElement('password', 'password_actual', [
            'label' => $this->translate('form_user_change-pass_pass-actual'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $notEmpty],
            'class' => 'text',
        ]);

        $password = $this->createElement('password', 'password', [
            'label' => $this->translate('form_user_change-pass_pass'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $notEmpty],
            'class' => 'text',
        ]);

        $password_repeat = $this->createElement('password', 'password_repeat', [
            'label' => $this->translate('form_user_change-pass_pass-repeat'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $vIdentival, $notEmpty],
            'class' => 'text',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_user_change-pass_submit'),
            // 'decorators' => $this->buttonDecoratorsCenturion,
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        // $main = new Zend_Form_SubForm();
        // $main->setDisableLoadDefaultDecorators(false);
        // $main->setDecorators( $this->subFormDecoratorsCenturion );
        // $main->addDisplayGroup( array( $hashSecurity, $password_actual, $password, $password_repeat, $submit ), 'content' );

        // $groupMain = $main->getDisplayGroup('content');
        // $groupMain->clearDecorators();
        // $groupMain->setLegend('Change password');
        // $groupMain->addDecorators( array(
        //     'FormElements',
        //     array( 'Fieldset', array( 'class' => 'form-group' ) ),
        // ));
        // $main->addAttribs( array( 'class' => 'form-main, changePassForm' ) );

        // $this->addSubForm( $main, 'main' );

        $this->addDisplayGroup(
            [$password_actual, $password, $password_repeat, $submit],
            'main'
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        // $this->_user->setFirstName($data['first_name']);
        // $this->_user->setLastName($data['last_name']);
        // $this->_user->setPhone($data['phone']);
        // $this->_user->setEmail($data['email']);
        $this->_user->setPassword($data['password']);

        return true;
    }

    // public function isValid($data){
    // 	$ret = parent::isValid($data);

 //        $password_actual = $this->main->password_actual->getValue();
 //        if( $this->user->getHashPassword($password_actual) != $this->user->password ){
 //            $this->main->password_actual->addError("Actual password not match");
 //            $ret = false;
 //        }

    // 	return $ret;
    // }
}
