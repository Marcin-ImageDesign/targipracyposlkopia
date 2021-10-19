<?php

class Event_Form_Stand_WwwSite extends Engine_Form
{
    /**
     * @var Inquiry
     */
    public $inquiry;

    protected $_tlabel = 'form_event-stand-www-site_';
    protected $_belong_to = 'EventFormStandWwwSite';

    /**
     * @var BaseUser
     */
    private $_baseUser;

    /**
     * @var ExhibStand
     */
    private $_exhibStand;

    public function setBaseUser($item)
    {
        $this->_baseUser = $item;
    }

    public function setExhibStand($item)
    {
        $this->_exhibStand = $item;
    }

    public function init()
    {
        $fields = [];

        $fields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email-address',
            'required' => false,
            'filters' => ['StringTrim', 'StripTags'],
            'allowEmpty' => false,
            'validators' => [
                ['EmailAddress', false],
            ],
        ]);

        $fields['submit'] = $this->createElement('submit', 'submit', [
            'label' => $this->translate('form_user_register_submit'),
            'ignore' => true,
            'class' => 'submit_send',
        ]);

        $this->addDisplayGroup($fields, 'main');

        $this->setAttrib('id', 'EventFormStandWwwSiteForm');
        $this->setAttrib('class', 'form ajaxForm');
    }

    protected function postIsValid($data)
    {
        $data = [];

        $fields = $this->getElements();
        foreach ($fields as $field) {
            if ($field instanceof Zend_Form_Element && 'submit' !== $field->getName()) {
                $data[$field->getName()] = $field->getValue();
            }
        }

        $subject = $data['email'];
        $data['name'] = '';
        $this->inquiry = Inquiry_Service_Manager::add(
            ExhibStand::INQUIRY_CHANNEL_WWW_SITE,
            $subject,
            $data,
            $this->_baseUser->getId(),
            $this->_exhibStand->getId()
        );

        // $this->sendInfoToUser(); //tymczasowo wyłączamy

        return true;
    }

    private function sendInfoToUser()
    {
        $smtp_options = $this->_baseUser->getSettingsSmtp();
        $view = $this->getView();

        $mail = new Engine_Mail($smtp_options);

        $view->mail_content = $view->render('stand/_mail-contact.phtml');
        $mail->setBodyHtml($view->render('_mail_layout.phtml'), 'utf-8');
        $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $view->mail_content)), 'utf-8');
        $mail->setSubject($view->translate('Zapytanie dotyczące oferty') . ' ' . DOMAIN);

        $recipientCount = 0;
        foreach ($this->_exhibStand->ExhibStandParticipation as $exhibStandParticipation) {
            $mail->addTo(
                $exhibStandParticipation->ExhibParticipation->User->getEmail(),
                $exhibStandParticipation->ExhibParticipation->User->getName()
            );
            ++$recipientCount;
        }

        if ($recipientCount > 0) {
            $mail->send();
        }
    }
}
