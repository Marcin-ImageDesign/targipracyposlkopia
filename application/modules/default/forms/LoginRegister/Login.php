<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 10.10.13
 * Time: 11:47
 * To change this template use File | Settings | File Templates.
 */
class Form_LoginRegister_Login extends Engine_Form
{
    protected $_belong_to = 'Login';

    /**
     * Number of seconds to expire the namespace.
     *
     * @var string
     */
    protected $_loginLifetime;

    /**
     * The validator class name.
     *
     * @var string
     */
    protected $_formValidator = 'Form_Validator_Login';

    /**
     * Constructor.
     *
     * @param array  $params        Params to instantiate the form validator
     * @param string $formName      Form name
     * @param string $loginLifetime Number of seconds to expire the namespace
     */
    public function __construct(array $params, $loginLifetime = 7200)
    {
        parent::__construct();

        // form settings
        $this->setAttribs(['class' => 'form ajaxForm', 'autocomplete' => 'off', 'id' => 'LoginForm']);

        $keys = ['tableName', 'loginColumn', 'passwordColumn'];
        $diff = array_diff_key(array_flip($keys), $params);

        if (0 !== count($diff)) {
            throw new Engine_Form_Exception('constructor array must have keys for ' . implode(' ', array_keys($diff)));
        }

        $login = $this->createElement('text', 'login', [
            'label' => $this->translate('form_default_login_login'),
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
        ]);

        $password = $this->createElement('password', 'password', [
            'label' => $this->translate('form_default_login_password'),
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                [
                    'validator' => 'StringLength',
                    'options' => ['min' => 3],
                    'breakChainOnFailure' => true,
                ],
                [new $this->_formValidator($params)],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_default_login_submit'),
            'type' => 'submit',
            'ignore' => true,
            'id' => 'submit',
            'value' => $this->translate('Log in'),
        ]);

        $this->addDisplayGroup(
            [$login, $password, $submit],
            'main'
        );

//        $this->setDecorators(array(
//            'FormElements',
//            array('HtmlTag', array('tag' => 'div', 'class' => 'form-class')),
//            'Form',
//        ));
//
//        $this->addElements( array( $login, $password, $submit ) );
    }
}
