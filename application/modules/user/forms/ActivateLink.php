<?php

/**
 * Description of ActivateLink.
 *
 * @author Marek Skotarek
 */
class User_Form_ActivateLink extends Engine_Form
{
    protected $_belong_to = 'UserFormActivateLink';

    public function init()
    {
        parent::init();

        // settings form
        $this->setAttribs(['class' => 'form ajaxForm', 'autocomplete' => 'off', 'id' => $this->getElementsBelongTo() . 'Form']);

        $notEmpty = new Zend_Validate_NotEmpty();
        $vEmailAddress = new Zend_Validate_EmailAddress();
        $vRecordExists = new Engine_Validate_RecordExists('User', 'email');

        $activateLinkMail = $this->createElement('text', 'activateLinkMail', [
            'label' => $this->translate('form_user_register_email'),
            'required' => false,
            'allowEmpty' => true,
            'filters' => ['StringTrim'],
            'validators' => [$vRecordExists, $vEmailAddress, $notEmpty],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_user_register_submit'),
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $this->addDisplayGroup(
            [$activateLinkMail, $submit],
            'main'
        );
    }

    public function postIsValid($data)
    {
        $data = $data[$this->getElementsBelongTo()];

        $user = User::findOneByEmailAndActive($data['activateLinkMail']);
        if ($user) {
            $this->getElement('activateLinkMail')->addError('Mail aktywny');
            $ret = false;
        } else {
            $ret = true;
        }

        return $ret;
    }
}
