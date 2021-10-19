<?php

class User_Form_SendToFriend extends Engine_Form
{
    protected $_belong_to = 'UserSendToFriend';

    private $_user;
    private $_stf;

    /**
     * @param User  $user
     * @param array $options
     * @param mixed $stf
     */
    public function __construct($user, $stf, $options = null)
    {
        parent::__construct($options);

        $advFields = [];

        $this->setAttribs(['class' => 'form ajaxForm', 'autocomplete' => 'off', 'id' => $this->getElementsBelongTo() . 'Form']);

        $this->_user = $user;
        $this->_stf = $stf;
        $notEmpty = new Zend_Validate_NotEmpty();
        $vEmailAddress = new Zend_Validate_EmailAddress();

        $params = [
            ['id_user', '=', $this->_user->getId()],
        ];
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('SendToFriend', 'email', $params);
        $vAlreadyTaken->setMessage($this->translate('form_user_senttofriend_email-double'));

        //$vAlreadyTaken2 = new Engine_Validate_AlreadyTaken('User', 'email' );
        //$vAlreadyTaken2->setMessage($this->translate('form_user_senttofriend_email-account-exists'));

        $email = $this->createElement('text', 'email', [
            'label' => $this->translate('form_user_senttofriend_email'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim', 'StripTags'],
            'validators' => [$notEmpty, $vEmailAddress, $vAlreadyTaken], //,$vAlreadyTaken2
        ]);

        $sender = $this->createElement('text', 'sender', [
            'label' => $this->translate('form_user_senttofriend_sender'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim', 'StripTags'],
            'validators' => [$notEmpty],
        ]);

        $name = $this->createElement('text', 'name', [
            'label' => $this->translate('form_user_senttofriend_name'),
            'required' => false,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => true,
            'class' => ' field-text',
            'filters' => ['StringTrim', 'StripTags'],
            'validators' => [$notEmpty],
        ]);

        $message = $this->createElement('textarea', 'message', [
            'label' => $this->translate('form_user_senttofriend_message'),
            'required' => false,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => true,
            'class' => ' field-text',
            //'filters'    => array( 'StringTrim' ),
            'validators' => [$notEmpty],
            'value' => $this->translate('form_user_senttofriend_message-default'),
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_user_senttofriend_submit'),
            // 'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $this->addDisplayGroup(
            [$email, $sender, $name, $message, $submit],
            'main'
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_stf->setEmail($data['email']);

        return true;
    }
}
