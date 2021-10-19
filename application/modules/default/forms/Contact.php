<?php

class Form_Contact extends Engine_Form
{
    protected $_tlabel = 'form_default_contact_';
    protected $_belong_to = 'DefaultFormContact';
    /**
     * @var BaseUser
     */
    private $_baseUser;

    public function setBaseUser($item)
    {
        $this->_baseUser = $item;
    }

    public function init()
    {
        $fields = [];

        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => true,
            'filters' => ['StringTrim', 'StripTags'],
            'allowEmpty' => false,
            'validators' => [
                ['StringLength', false, ['min' => 1, 'max' => 255]],
            ],
        ]);

        $fields['company'] = $this->createElement('text', 'company', [
            'label' => $this->_tlabel . 'company',
            'required' => true,
            'filters' => ['StringTrim', 'StripTags'],
            'allowEmpty' => false,
            'validators' => [
                ['StringLength', false, ['min' => 1, 'max' => 255]],
            ],
        ]);

        $fields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email-address',
            'required' => true,
            'filters' => ['StringTrim', 'StripTags'],
            'allowEmpty' => false,
            'validators' => [
                ['EmailAddress', false],
            ],
        ]);

        $fields['phone'] = $this->createElement('text', 'phone', [
            'label' => $this->_tlabel . 'phone',
            'required' => false,
            'filters' => ['StringTrim', 'StripTags'],
            'allowEmpty' => true,
            'validators' => [
                ['StringLength', false, ['min' => 0, 'max' => 64]],
            ],
        ]);

        $fields['submit'] = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $this->addDisplayGroup($fields, 'main');

        $this->setAttrib('id', 'DefaultFormContact');
        $this->setAttrib('class', 'form ajaxForm');
    }

    protected function postIsValid($data)
    {
        $data = $data['DefaultFormContact'];

        $this->sendInfoToUser($data);

        return true;
    }

    private function sendInfoToUser($data)
    {
        $smtp_options = $this->_baseUser->getSettingsSmtp();
        $view = $this->getView();
        $view->data = $data;

        $mail = new Engine_Mail($smtp_options);
        $view->mail_content = $view->render('index/_mail-contact.phtml');
        $mail->setBodyHtml($view->render('_mail_layout.phtml'), 'utf-8');
        $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $view->mail_content)), 'utf-8');
        $mail->setSubject($view->translate('Prośba o rezerwację stoiska') . ' ' . DOMAIN);

        $mail->addTo($smtp_options['email_to']);
        $mail->send(true);
    }
}
