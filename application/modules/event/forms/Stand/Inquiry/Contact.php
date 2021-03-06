<?php

class Event_Form_Stand_Inquiry_Contact extends Engine_FormIframe
{
    /**
     * @var Inquiry
     */
    public $inquiry;
    /**
     * @var BaseUser
     */
    protected $_baseUser;

    /**
     * @var ExhibStand
     */
    protected $_exhibStand;

    /**
     * @var User
     */
    protected $_user;

    protected $_belong_to = 'EventFormStandInquiryContact';

    protected $_tlabel = 'form_event-stand-inquiry-contact_';

    public function setBaseUser($item)
    {
        $this->_baseUser = $item;
    }

    public function setExhibStand($item)
    {
        $this->_exhibStand = $item;
    }

    public function setUser($item)
    {
        $this->_user = $item;
    }

    public function init()
    {
        $fieldsAside = null;
        $fields = [];

        // odczyt z danych zalogowanego usera
        $nameUser = '';
        if ($this->_user) {
            $name = $this->_user->getFirstName() . ' ' . $this->_user->getLastName();
            $email = $this->_user->getEmail();
            $phone = $this->_user->getPhone();
            $city = $this->_user->getCity();
        }

        $fields['id_stand'] = $this->createElement('hidden', 'id_stand', [
            'value' => $this->_exhibStand->getId(),
            'allowEmpty' => false,
            'required' => true,
        ]);

        if (!empty($name)) {
            $nameUser = $name;
        }
        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => false,
            'filters' => ['StringTrim', 'StripTags'],
            'value' => $nameUser,
            'allowEmpty' => true,
            'validators' => [
                ['StringLength', false, ['min' => 1, 'max' => 255]],
            ],
        ]);

        $emailUser = '';
        // odczyt ciasteczka z adresem email
        $request = new Zend_Controller_Request_Http();
        $emailUser = $request->getCookie('emailUser');
        if (!empty($email)) {
            $emailUser = $email;
        }

        $fields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email',
            'required' => true,
            'filters' => ['StringTrim', 'StripTags'],
            'value' => $emailUser,
            'allowEmpty' => false,
            'validators' => [
                ['EmailAddress', false],
                ['StringLength', false, ['min' => 1, 'max' => 255]],
            ],
        ]);

        $phoneUser = '';
        if (!empty($phone)) {
            $phoneUser = $phone;
        }

        $fields['phone'] = $this->createElement('text', 'phone', [
            'label' => $this->_tlabel . 'phone',
            'required' => false,
            'filters' => ['StringTrim', 'StripTags'],
            'value' => $phoneUser,
            'allowEmpty' => true,
            'validators' => [
                ['StringLength', false, ['min' => 1, 'max' => 255]],
            ],
        ]);

        $cityUser = '';
        if (!empty($city)) {
            $cityUser = $city;
        }

        $fields['city'] = $this->createElement('text', 'city', [
            'label' => $this->_tlabel . 'city',
            'required' => false,
            'filters' => ['StringTrim', 'StripTags'],
            'value' => $cityUser,
            'allowEmpty' => true,
            'validators' => [
                ['StringLength', false, ['min' => 1, 'max' => 255]],
            ],
        ]);

        $fields['message'] = $this->createElement('textarea', 'message', [
            'label' => $this->_tlabel . 'message',
            'required' => false,
            'filters' => ['StringTrim', 'StripTags'],
            'allowEmpty' => true,
            'validators' => [
                ['StringLength', false, ['min' => 1, 'max' => 10000]],
            ],
        ]);

        $fieldsAside['accept_data'] = $this->createElement('checkbox', 'accept_data', [
            'label' => $this->translate('form_event_contact_comm_rules'),
            'required' => true,
            'uncheckedValue' => null,
        ]);

        $fieldsAside['submit'] = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
            'class' => 'submit_send_2 btnYellow',
        ]);

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'legend' => $this->_tlabel . 'group-contact',
            ]
        );

        $this->addDisplayGroup($fieldsAside, 'aside');

        $this->setAttrib('class', 'form ajaxForm');
    }

    /**
     * @param mixed $data
     *
     * @return Inquiry
     */
    protected function postIsValid($data)
    {
        $view = $this->getView();

        $subject = $view->translate('Stand inquiry');
        $data = [];

        $fields = $this->getElements();
        foreach ($fields as $field) {
            if ($field instanceof Zend_Form_Element && 'submit' !== $field->getName()) {
                $data[$field->getName()] = $field->getValue();
            }
        }

        $this->inquiry = Inquiry_Service_Manager::add(
            ExhibStand::INQUIRY_CHANNEL,
            $subject,
            $data,
            $this->_baseUser->getId(),
            $this->_exhibStand->getId()
        );

        $this->sendInfoToUser($data);

        // zapis ciasteczka z adresem email
        $emailUser = $data['email'];
        if (!empty($emailUser)) {
            setcookie('emailUser', $emailUser, time() + 3600 * 30, '/');
        }

        return true;
    }

    private function sendInfoToUser($data)
    {
        $redirect_email = Engine_Variable::getInstance()->getVariable(Variable::EMAIL_REDIRECT_FORM, $this->_baseUser->getId());
        $smtp_options = $this->_baseUser->getSettingsSmtp();
        $view = $this->getView();
        $view->data = $data;
        $mail = new Engine_Mail($smtp_options);

        $view->mail_content = $view->render('stand/_mail-contact.phtml');
        $mail->setBodyHtml($view->render('_mail_layout.phtml'), 'utf-8');
        $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $view->mail_content)), 'utf-8');
        $mail->setSubject($view->translate('Zapytanie dotycz??ce oferty') . ' ' . DOMAIN . ' - ' . $this->_exhibStand->getName());

        $recipientCount = 0;
        $receivers = [];
        foreach ($this->_exhibStand->ExhibStandParticipation as $key => $exhibStandParticipation) {
            if ($exhibStandParticipation->getIsActive()) {
                $receivers[$key]['email'] = $exhibStandParticipation->ExhibParticipation->User->getEmail();
                $receivers[$key]['name'] = $exhibStandParticipation->ExhibParticipation->User->getName();
            }
        }
        if (empty($redirect_email)) {
            foreach ($receivers as $receiver) {
                $mail->addTo($receiver['email'], $receiver['name']);
                ++$recipientCount;
            }
        } else {
            $mail->addTo($redirect_email, $this->_exhibStand->getName());
            //zend mail nie obs??uguje wielu replyTo
            $mail->setReplyTo($receivers[0]['email'], $receivers[0]['name']);

            $recipientCount = 1;
        }
        if ($recipientCount > 0) {
            $mail->send();
        }
    }
}
