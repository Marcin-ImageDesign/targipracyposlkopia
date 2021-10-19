<?php

class Form_CourseContact extends Engine_Form
{
    const FORM_NAME = 'courseContact-form';

    const PRE_FIELD_NAME = 'courseContact_';

    public function init()
    {
        $notEmpty = new Zend_Validate_NotEmpty();
        $notEmpty->setMessage('Podanie pola jest wymagane.');

        $vEmailAddress = new Zend_Validate_EmailAddress();

        $name = $this->createElement('text', self::PRE_FIELD_NAME . 'name', [
            'required' => true,
            'decorators' => $this->elementDecoratorsTrTd,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'value' => 'Imię',
        ]);
        $name->setAttrib('style', 'width:171px;');
        $name->setAttrib('onclick', "(this.value=='Imię'?this.value='':'')");
        $name->setAttrib('onkeyup', "(this.value=='Imię'?this.value='':'')");
        $name->setAttrib('escape', false);
        $name->removeDecorator('Label');

        $surname = $this->createElement('text', self::PRE_FIELD_NAME . 'surname', [
            'required' => true,
            'decorators' => $this->elementDecoratorsTrTd,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'value' => 'Nazwisko',
        ]);
        $surname->setAttrib('style', 'width:171px;');
        $surname->setAttrib('onclick', "(this.value=='Nazwisko'?this.value='':'')");
        $surname->setAttrib('onkeyup', "(this.value=='Nazwisko'?this.value='':'')");
        $surname->removeDecorator('Label');

        $email = $this->createElement('text', self::PRE_FIELD_NAME . 'email', [
            'required' => true,
            'decorators' => $this->elementDecoratorsTrTd,
            'filters' => ['StringTrim'],
            'validators' => [$vEmailAddress],
            'value' => 'E-mail',
        ]);
        $email->setAttrib('style', 'width:171px;');
        $email->setAttrib('onclick', "(this.value=='E-mail'?this.value='':'')");
        $email->setAttrib('onkeyup', "(this.value=='E-mail'?this.value='':'')");
        $email->setErrorMessages(['Proszę podać poprawny adres e-mail.']);
        $email->removeDecorator('Label');

        $company = $this->createElement('text', self::PRE_FIELD_NAME . 'company', [
            'required' => true,
            'decorators' => $this->elementDecoratorsTrTd,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'value' => 'Firma',
        ]);
        $company->setAttrib('style', 'width:171px;');
        $company->setAttrib('onclick', "(this.value=='Firma'?this.value='':'')");
        $company->setAttrib('onkeyup', "(this.value=='Firma'?this.value='':'')");
        $company->removeDecorator('Label');

        $message = $this->createElement('textarea', self::PRE_FIELD_NAME . 'message', [
            'required' => true,
            'decorators' => $this->elementDecoratorsTrTd,
            'filters' => ['StringTrim'],
            'validators' => [$notEmpty],
            'value' => 'Treść...',
        ]);
        $message->setAttrib('style', 'width:171px;height:100px;');
        $message->setAttrib('onclick', "(this.value=='Treść...'?this.value='':'')");
        $message->setAttrib('onkeyup', "(this.value=='Treść...'?this.value='':'')");
        $message->removeDecorator('Label');

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Wyślij',
            'decorators' => $this->buttonDecoratorsTrTd,
            'ignore' => true,
            'class' => 'submit_send',
        ]);
        $submit->removeDecorator('Label');

        $this->setDecorators([
            'FormElements',
            ['HtmlTag', ['tag' => 'table', 'class' => 'formTable', 'style' => 'width: 100%']],
            'Form',
        ]);

        $this->addElements([$name, $surname, $email, $company, $message, $submit]);
    }
}
