<?php

class Form_Newsletter extends Engine_Form
{
    const FORM_NAME = 'newsletter-form';

    public function init()
    {
        $options = null;
        $this->setAction('#newsletter');

        $notEmpty = new Zend_Validate_NotEmpty();
        $notEmpty->setMessage('Podanie pola jest wymagane.');

        $vEmailAddress = new Zend_Validate_EmailAddress();
        $vEmailAddress->setMessage('Proszę podać poprawny adres e-mail.', 'emailAddressInvalidFormat');
        $baseUser = Zend_Registry::get('BaseUser');

        $options[] = ['id_base_user', '=', $baseUser->getId()];
        $vAlreadyTaken = new Engine_Validate_AlreadyTaken('Newsletter', 'email', $options);
        $vAlreadyTaken->setMessage('Podany adres e-mail jest już zapisany.');

        $email = $this->createElement('text', 'email', [
            'required' => true,
            'decorators' => $this->elementDecoratorsEmpty,
            'filters' => ['StringTrim'],
            'validators' => [$vAlreadyTaken, $vEmailAddress, $notEmpty],
            'class' => 'text newsletter',
            'attribs' => [
                'style' => '',
            ],
            'value' => 'E-mail',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Wyślij',
            'decorators' => $this->buttonDecoratorsEmpty,
            'ignore' => true,
            'class' => 'submit_send',
            'attribs' => ['style' => 'float:right;margin-top:6px;'],
        ]);

        $this->setDecorators([
            'FormElements',
            ['HtmlTag'],
            'Form',
        ]);

        $this->addElements([$email, $submit]);
    }
}
