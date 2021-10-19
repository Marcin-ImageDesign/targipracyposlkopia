<?php

class User_Form_User extends Engine_Form
{
    protected $_belong_to = 'UserFormUser';

    protected $_tlabel = 'form_user_form_';

    /**
     * @var User
     */
    protected $user;

    /**
     * @param User  $user
     * @param array $options
     */
    public function init()
    {
        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');
        $this->addMainGroup();
        $this->addRole();
        $this->addPassword();
        $this->addAdv();
    }

    public function addMainGroup()
    {
        $mainFields = null;
        $vAlreadyTakenParams = [];
        if (!$this->user->isNew()) {
            $vAlreadyTakenParams[] = ['id_user', '!=', $this->user->getId()];
        }

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('User', 'email', $vAlreadyTakenParams);

        $mainFields['first_name'] = $this->createElement('text', 'first_name', [
            'label' => $this->_tlabel . 'first-name',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->user->getFirstName(),
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $mainFields['last_name'] = $this->createElement('text', 'last_name', [
            'label' => $this->_tlabel . 'last-name',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->user->getLastName(),
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $mainFields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->user->getEmail(),
            'validators' => [
                ['NotEmpty', true],
                ['EmailAddress', true],
                [$vAlreadyTaken, true], ],
        ]);

        $mainFields['adv_company'] = $this->createElement('text', 'adv_company', [
            'label' => $this->_tlabel . 'adv_company',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->user->getCompany(),
        ]);

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];

        $mainFields['is_active'] = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is_active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->user->is_active,
            'validators' => [
                ['InArray', true, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $mainFields['is_banned'] = $this->createElement('select', 'is_banned', [
            'label' => $this->_tlabel . 'is-blocked',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->user->is_banned,
            'validators' => [
                ['InArray', true, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $this->addDisplayGroup(
            $mainFields,
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_user_info',
            ]
        );
    }

    public function addPassword()
    {
        $passwordFields = null;
        $vStringLength = new Zend_Validate_StringLength(['min' => 6, 'max' => 20]);
        $vIdentival = new Zend_Validate_Identical('password');

        $passwordFields['password'] = $this->createElement('password', 'password', [
            'label' => $this->_tlabel . 'password',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                [$vStringLength, true],
                ['NotEmpty', true], ],
        ]);

        $passwordFields['password_repeat'] = $this->createElement('password', 'password_repeat', [
            'label' => $this->_tlabel . 'repeat-password',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                [$vStringLength, true],
                [$vIdentival, true],
                ['NotEmpty', true], ],
        ]);

        if (!$this->user->isNew()) {
            $passwordFields['password']->setRequired(false);
            $passwordFields['password']->setAllowEmpty(true);
            $passwordFields['password_repeat']->setRequired(false);
            $passwordFields['password_repeat']->setAllowEmpty(true);
            $passwordFields['password']->setDescription('Insert new password to change');
        }

        $this->addDisplayGroup(
            $passwordFields,
            'main-pass',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_password',
            ]
        );
    }

    public function addAdv()
    {
        $advancedFields = null;
        $advancedFields['phone'] = $this->createElement('text', 'phone', [
            'label' => $this->_tlabel . 'phone',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->user->getPhone(),
        ]);

        $advancedFields['city'] = $this->createElement('text', 'city', [
            'label' => $this->_tlabel . 'city',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->user->getCity(),
        ]);

        $advancedFields['post_code'] = $this->createElement('text', 'post_code', [
            'label' => $this->_tlabel . 'post-code',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->user->getPostCode(),
        ]);

        $advancedFields['street'] = $this->createElement('text', 'street', [
            'label' => $this->_tlabel . 'street',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->user->getStreet(),
        ]);

        $advancedFields['nip'] = $this->createElement('text', 'nip', [
            'label' => $this->_tlabel . 'nip',
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'value' => $this->user->getNip(),
        ]);

        $this->addDisplayGroup(
            $advancedFields,
            'main-adv',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_advanced',
            ]
        );
    }

    public function addRole()
    {
        $roleFields = null;
        $userRoleListOptions = null;
        $userRoleListOptionsResults = Doctrine_Query::create()
            ->from('UserRole ur')
            ->execute()
        ;
        foreach ($userRoleListOptionsResults as $userRoleListOptionsResult) {
            $userRoleListOptions[$userRoleListOptionsResult->id_user_role] = $this->translate('label_admin_user_role_' . $userRoleListOptionsResult->id_user_role);
        }
        $roleFields['id_user_role'] = $this->createElement('select', 'id_user_role', [
            'label' => $this->_tlabel . 'role',
            'multiOptions' => $userRoleListOptions,
            'allowEmpty' => false,
            'class' => 'select-user-filter',
            'value' => $this->user->id_user_role,
            'validators' => [
                ['InArray', false, [array_keys($userRoleListOptions)]],
            ],
        ]);

        $this->addDisplayGroup(
            $roleFields,
            'main-role',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_role',
            ]
        );
    }

    public function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        //populate main
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];
        $this->user->company = $data['company'];
        $this->user->position = $data['position'];
        $this->user->is_active = (bool) $data['is_active'];
        $this->user->is_banned = (bool) $data['is_banned'];

        //jeśli blokada, wylogowujemy użytkownika
        if ($this->user->is_banned) {
            $this->logutBannedUser();
        }

        //populate password
        $pass = $this->password->getValue();
        if (!empty($pass)) {
            $this->user->password = $this->user->getHashPassword($data['password']);
        }

        //populate adv
        $this->user->phone = $data['phone'];
        $this->user->company = $data['adv_company'];
        $this->user->city = $data['city'];
        $this->user->post_code = $data['post_code'];
        $this->user->street = $data['street'];
        $this->user->nip = $data['nip'];

        //populate role
        $this->user->id_user_role = $data['id_user_role'];

        return true;
    }

    /**
     * @param $user User
     */
    protected function setUser($user)
    {
        $this->user = $user;
    }

    //wylogowanie zablokowanego użytkownika
    private function logutBannedUser()
    {
        $session_id = Doctrine_Query::create()
            ->select('s.id')
            ->from('Session s')
            ->where('s.id_user = ?', [$this->user->getId()])
            ->execute([], Doctrine::HYDRATE_SINGLE_SCALAR)
        ;

        if (count($session_id) > 0) {
            $delete = Doctrine_Query::create()
                ->delete('Session s')
                ->where('s.id = ?', [$session_id])
                ->execute()
            ;
        } else {
            return;
        }
    }
}
