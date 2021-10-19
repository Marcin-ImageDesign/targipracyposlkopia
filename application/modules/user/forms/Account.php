<?php

class User_Form_Account extends Engine_Form
{
    protected $_belong_to = 'UserEditData';

    private $_user;

    private $_shortName;

    /**
     * @param User $user
     * @param array $options
     * @param null|mixed $shortName
     */
    public function __construct($user, $options = null, $shortName = null)
    {
        parent::__construct($options);

        $advFields = [];

        $this->setAttribs(['class' => 'form', 'autocomplete' => 'off', 'id' => $this->getElementsBelongTo() . 'Form']);

        $this->_shortName = $shortName;

        $this->_user = $user;
        $notEmpty = new Zend_Validate_NotEmpty();
        $vEmailAddress = new Zend_Validate_EmailAddress();

        $params = [
            ['id_base_user', '=', $user->id_base_user],
            ['id_user', '!=', $user->getId()],
        ];

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('User', 'email', $params);
        $vOnlyNumber = new Zend_Validate_Digits();

        $email = $this->createElement('text', 'email', [
            'label' => 'E-mail',
            'required' => false,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => true,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty, $vEmailAddress, $vAlreadyTaken],
            'value' => $user->getEmail(),
            'disabled' => true,
        ]);
        $email->getValidator('EmailAddress')->setMessage($this->getView()->translate('form_user_register_invalid_email'));

        $firstName = $this->createElement('text', 'first_name', [
            'label' => $this->translate('form_user_account_first-name'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'value' => $user->getFirstName(),
        ]);

        $lastName = $this->createElement('text', 'last_name', [
            'label' => $this->translate('form_user_account_last-name'),
            'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'value' => $user->getLastName(),
        ]);

        $phone = $this->createElement('text', 'phone', [
            'label' => $this->translate('form_user_account_phone'),
            // 'required' => true,
            // 'decorators' => $this->elementDecoratorsCenturion,
            'filters' => ['StringTrim'],
            // 'validators' => array( $notEmpty ),
            'class' => 'text',
            'value' => $user->getPhone(),
        ]);

        $fileDescriptionSize = $this->getView()->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB';

        $image = $this->createElement('FileImage', 'img', [
            'label' => $this->translate('form_user_account_image'),
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        $image->getDecorator('row')->setOption('class', 'form-item img_content');

        if ($user->hasImage()) {
            $fileImageDecorator = $image->getDecorator('FileImage');
            $fileImageDecorator->setOptions([
                'image' => Service_Image::getUrl($user->getIdImage(), 40, 40, 'a'),
            ]);
        }

        $advFields['adv_company'] = $this->createElement('text', 'adv_company', [
            'label' => $this->translate('form_user_account_adv_company'),
            'required' => false,
            'allowEmpty' => true,
            'class' => 'text',
            'value' => $user->getCompany(),
        ]);

        $advFields['city'] = $this->createElement('text', 'city', [
            'label' => $this->translate('form_user_account_city'),
            'required' => false,
            'allowEmpty' => true,
            'class' => 'text',
            'value' => $user->getCity(),
        ]);

        $advFields['post_code'] = $this->createElement('text', 'post_code', [
            'label' => $this->translate('form_user_account_post_code'),
            'required' => false,
            'allowEmpty' => true,
            'class' => 'text',
            'value' => $user->getPostCode(),
        ]);

        $advFields['street'] = $this->createElement('text', 'street', [
            'label' => $this->translate('form_user_account_street'),
            'required' => false,
            'allowEmpty' => true,
            'class' => 'text',
            'description' => '<hr style="width:372px;float:left; color:#C2C2C2; margin-top:5px;margin-bottom:10px">',
            'value' => $user->getStreet(),
        ]);
        $advFields['street']->getDecorator('Description')->setEscape(false);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_user_account_submit'),
            // 'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $pola = [$firstName, $lastName, $email, $image];

        $this->addDisplayGroup(
            $pola,
            'main'
        );

        $this->addDisplayGroup(
            [$submit],
            'main2'
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        $this->_user->setFirstName($data['first_name']);
        $this->_user->setLastName($data['last_name']);
        $this->_user->setPhone($data['phone']);

        $this->_user->setStreet($data['street']);
        if ($this->_user->getEmail() !== $data['email']) {
            $this->_user->setEmailNew($data['email']);
        } else {
            $this->_user->setEmailNew(null);
        }

        return true;
    }

    // $this->setDecorators(array(
    //     'FormElements',
    //     'Form',
    // ));

    // $main = new Zend_Form_SubForm();
    // $main->setDisableLoadDefaultDecorators(false);
    // $main->setDecorators( $this->subFormDecoratorsCenturion );
    // $main->addDisplayGroup( array( $email, $firstName, $lastName, $phone, $submit ), 'content' );

    // $groupMain = $main->getDisplayGroup('content');
    // $groupMain->clearDecorators();
    // $groupMain->setLegend('Edit account');
    // $groupMain->addDecorators( array(
    //     'FormElements',
    //     array( 'Fieldset', array( 'class' => 'form-group' ) ),
    // ));
    // $main->addAttribs( array( 'class' => 'form-main' ) );

    // $this->addSubForm( $main, 'main' );

    // }
}
