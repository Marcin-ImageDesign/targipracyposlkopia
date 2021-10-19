<?php

class User_Form_Register extends Engine_Form
{
    protected $_belong_to = 'UserRegister';

    /**
     * @var User
     */
    private $_user;

    /**
     * @param User $user
     */
    public function __construct($user)
    {
        $options = null;
        parent::__construct();

        // settings form
        $this->setAttribs(['class' => 'form ajaxForm', 'autocomplete' => 'off', 'id' => $this->getElementsBelongTo() . 'Form']);

        $this->_user = $user;

        $notEmpty = new Zend_Validate_NotEmpty();
        $vEmailAddress = new Zend_Validate_EmailAddress();
        $vStringLength = new Zend_Validate_StringLength(['min' => 6, 'max' => 20]);

        $vIdentival = new Zend_Validate_Identical('password');
        $baseUser = Zend_Registry::get('BaseUser');

        $options[] = ['id_base_user', '=', $baseUser->getId()];
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('User', 'email', $options);

        $first_name = $this->createElement('text', 'first_name', [
            'label' => $this->translate('form_user_register_first-name'),
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'class' => 'text',
        ]);

        $last_name = $this->createElement('text', 'last_name', [
            'label' => $this->translate('form_user_register_last-name'),
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'class' => 'text',
        ]);

        $last_name->addErrorMessage('Pole jest wymagane');

        $phone = $this->createElement('text', 'phone', [
            'label' => $this->translate('form_user_register_phone'),
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'class' => 'text',
        ]);

        $email = $this->createElement('text', 'email', [
            'label' => $this->translate('form_user_register_email'),
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [$vAlreadyTaken, $vEmailAddress, $notEmpty],
            'class' => 'text',
        ]);
        $email->getValidator('EmailAddress')->setMessage($this->getView()->translate('form_user_register_invalid_email'));

        $company = $this->createElement('text', 'adv_company', [
            'label' => $this->translate('form_user_account_adv_company'),
            'required' => false,
            'allowEmpty' => true,
            'class' => 'text',
        ]);

        $password = $this->createElement('password', 'password', [
            'label' => $this->translate('form_user_register_password'),
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $notEmpty],
            'class' => 'text',
        ]);

        $password_repeat = $this->createElement('password', 'password_repeat', [
            'label' => $this->translate('form_user_register_repeat-password'),
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [$vStringLength, $vIdentival, $notEmpty],
            'class' => 'text',
        ]);

        $accept_data = $this->createElement('checkbox', 'accept_data', [
            'label' => $this->translate('form_user_register_com_prersonal-data'),
            'required' => true,
            'uncheckedValue' => null,
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_user_register_submit'),
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $this->addDisplayGroup(
            [$first_name, $last_name, $phone, $email, $company, $password, $password_repeat],
            'main'
        );

        $this->addDisplayGroup(
            [$accept_data, $submit],
            'aside'
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_user->setFirstName($data['first_name']);
        $this->_user->setLastName($data['last_name']);
        $this->_user->setPhone($data['phone']);
        $this->_user->setEmail($data['email']);
        $this->_user->setPassword($data['password']);
        $this->_user->setCompany($data['adv_company']);
        $this->_user->setAcceptData($data['accept_data']);

        $userConfirmRequest = new UserConfirmRequest();
        $userConfirmRequest->hash = Engine_Utils::_()->getHash();
        $userConfirmRequest->setType(UserConfirmRequest::ACCOUNT_ACTIVATION);

        $this->_user->UserConfirmRequest->add($userConfirmRequest);

        return true;
    }
}
