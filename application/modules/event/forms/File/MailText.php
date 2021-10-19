<?php

class Event_Form_File_MailText extends Engine_FormAdmin
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @param Event $event
     * @param array $options
     */
    public function __construct($event, $options = null)
    {
        parent::__construct($options);
        $this->event = $event;

        $downloadMailText = $this->createElement('tinyMce', 'download_mail_text', [
            'required' => false,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => true,
            'class' => ' field-text',
            'value' => $this->event->getDownloadMailText(),
            'filters' => ['StringTrim'],
        ]);
        $downloadMailTextDecorator = $downloadMailText->getDecorator('data');
        $downloadMailTextDecorator->setOption('style', 'width: 600px;');

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->setDecorators([
            'Description',
            'FormElements',
            'Form',
        ]);

        // nagłówek
        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$downloadMailText, $submit], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->setLegend('Text');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        // dodanie subformularzu do formularza
        $this->addSubForm($main, 'main');
    }
}
