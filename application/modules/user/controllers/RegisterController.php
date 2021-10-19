<?php

class User_RegisterController extends Engine_Controller_Frontend
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @var UserConfirmRequest
     */
    private $_userConfirmRequest;

    /**
     * @var Form_Register
     */
    private $formRegister;

    //zmienne dla autologowania
    private $_authAdapter = 'Engine_Auth_Adapter_Doctrine';
    private $_tableName = 'User';
    private $_loginColumn = 'email';
    private $_passwordColumn = 'password';
    private $_skipPass = true;

    public function postDispatch()
    {
        parent::postDispatch();
//        if(!$this->_helper->viewRenderer->getNoRender()) {
//            if($this->_helper->layout->isEnabled()) {
//                if('hall' !== $this->_request->getActionName()){
//                    $this->renderExhibitorsToPlaceholder('event/_section_nav.phtml', 'section-nav-content');
//                }
//
//            }
//        }
        if ($this->_helper->layout->isEnabled()) {
            $this->renderNewsToPlaceholder('news/_section_nav.phtml', 'section-nav-content');
        }
    }

    public function indexAction()
    {
        $engineUtils = Engine_Utils::_();
        $this->_user = new User();
        $this->_user->BaseUser = $this->getSelectedBaseUser();
        $this->_user->hash = $engineUtils->getHash();
        $this->_user->id_user_role = UserRole::ROLE_USER;
        $this->_user->is_active = false;

        $this->formRegister = new User_Form_Register($this->_user);
        $this->view->formRegister = $this->formRegister;

        if ($this->_request->isPost()) {
            if ($this->formRegister->isValid($this->_request->getPost())) {
                $this->_user->save();

                $this->sendInfoToAdmin();
                $this->sendActivateLinkToUser();

                $url = $this->view->url(
                    ['event_uri' => $this->getSelectedEvent()->getUri()],
                    'user_register_thx'
                );

                if ($this->displayJsonResponse) {
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $this->view->render('register/thx.phtml');
                    $this->jsonResult['content_id'] = $this->formRegister->getElementsBelongTo() . 'Form';
                } else {
                    $this->_redirector->gotoUrlAndExit($url);
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $this->formRegister->render();
                $this->jsonResult['content_id'] = $this->formRegister->getElementsBelongTo() . 'Form';
            }
        }
    }

    public function thxAction()
    {
    }

    public function sendActivateLinkAction()
    {
        $form = new User_Form_ActivateLink();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $post = $form->getValues($form->getElementsBelongTo());
                $user = User::findOneByEmail($post['activateLinkMail']);
                $this->_user = $user;

//                $this->sendInfoToAdmin();
                // wyślij maila z linkiem na podany adres
                $this->sendActivateLinkToUser();

                if ($this->displayJsonResponse) {
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $this->view->render('register/thx-send-activate-link.phtml');
                    $this->jsonResult['content_id'] = $form->getElementsBelongTo() . 'Form';
                } else {
                    if ($this->getSelectedEvent()->getUri()) {
                        $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'login');
                    } else {
                        $this->_redirector->gotoRouteAndExit();
                    }
                    $message = $this->view->translate('label_user_resend-activate-link_ok');
                    $this->_flash->success->addMessage($message);
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $form->render();
                $this->jsonResult['content_id'] = $form->getElementsBelongTo() . 'Form';
            }
        }

        $this->view->formSendActivateLink = $form;
    }

    public function thxSendActivateLinkAction()
    {
    }

    public function activateAccountAction()
    {
        $this->_userConfirmRequest = UserConfirmRequest::findOneByHashAndType(
            $this->_getParam('userConfirmRequestHash'),
            UserConfirmRequest::ACCOUNT_ACTIVATION
        );

        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_userConfirmRequest, 'UserConfirmRequest Not Found hash: (' . $this->_getParam('userConfirmRequestHash') . ')');

        $this->_user = $this->_userConfirmRequest->User;
        $message = $this->view->translate('label_user_register_activate_account_error');

        if (($this->_user && !$this->_user->is_banned) && ($this->_userConfirmRequest)) {
            if ($this->_user->is_active) {
                $message = $this->view->translate('label_user_register_activate_account_allready');
            } elseif (!$this->_user->is_active && $this->_userConfirmRequest->is_used) {
                $message = $this->view->translate('label_user_register_activate-account_link-inactive');
            } else {
                // Statystyki - punkty za rejestrację podstawową
                Statistics_Service_Manager::add(Statistics::CHANNEL_ACCOUNT_REGISTRATION, null, $this->getSelectedEvent()->getId(), [], null, $this->_user->getId());

                // sprawdzenie czy zostałl wysłane zaproszenie do tej osoby
                $stf = SendToFriend::findByEmail($this->_user->getEmail());
                if (!empty($stf[0])) {
                    // Statystyki - punkty dla osoby potwierdzającej
                    Statistics_Service_Manager::add(Statistics::CHANNEL_ACCOUNT_REGISTRATION_WITH_INVITATION, null, $this->getSelectedEvent()->getId(), [], null, $this->_user->getId());
                    foreach ($stf as $user2) {
                        // Statystyki - punkty dla osób wysyłających zaproszenie
                        Statistics_Service_Manager::add(Statistics::CHANNEL_ACCOUNT_INVITATION, $user2['id_user'], $this->getSelectedEvent()->getId(), [], null, $user2['id_user']);
                    }
                }

                $this->_user->is_active = true;
                $this->_user->save();

                $used_at = new Zend_Date();
                $used_at = $used_at->toString('YYYY-MM-dd HH:mm:ss');
                $this->_userConfirmRequest->setIsUsed(true);
                $this->_userConfirmRequest->setUsedAt($used_at);
                $this->_userConfirmRequest->save();
                $message = $this->view->translate('label_user_register_activate_account_ok');
                $this->autoLogin();
            }
        } elseif ($this->_user->is_banned) {
            $message = $this->view->translate('label_user_register_activate-account_user-banned');
        }
        $this->view->message = $message;
    }

    private function sendInfoToAdmin()
    {
        $smtp_options = $this->getSelectedBaseUser()->getSettingsSmtp();

        $mail = new Engine_Mail($smtp_options);

        $this->view->user = $this->_user;
        $this->view->mail_content = $this->view->render('register/_mail-info-admin.phtml');
        $mail->setBodyHtml($this->view->render('_mail_layout.phtml'), 'utf-8');
        $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $this->view->mail_content)), 'utf-8');
        $mail->setSubject($this->view->translate('User registered on site') . ' ' . DOMAIN);
        $mail->setDefaultRecipients();

        $mail->send();
    }

    private function sendActivateLinkToUser()
    {
        $engineUtils = Engine_Utils::_();

        // kasowanie poprzednich linków
        $updateLinkQuery = Doctrine_Query::create()
            ->update('UserConfirmRequest ucr')
            ->where('ucr.id_user = ?', $this->_user->getId())
            ->addWhere('ucr.is_used = 0')
            ->addWhere('ucr.type = ?', UserConfirmRequest::ACCOUNT_ACTIVATION)
            ->set('ucr.is_used', '?', 1)
       ;

        $updateLinkQuery->execute();

        $userConfirmRequest = new UserConfirmRequest();
        $userConfirmRequest->hash = $engineUtils->getHash();
        $userConfirmRequest->id_user = $this->_user->getId();
        $userConfirmRequest->setType(UserConfirmRequest::ACCOUNT_ACTIVATION);
        $userConfirmRequest->save();

        $smtp_options = $this->getSelectedBaseUser()->getSettingsSmtp();
        $mail = new Engine_Mail($smtp_options);

        $this->view->user = $this->_user;
        $this->view->userConfirmRequest = $userConfirmRequest;

        $this->view->selectedEvent = $this->getSelectedEvent();
        $this->view->mail_content = $this->view->render('register/_mail-activate-link-user.phtml');
        $mail->setBodyHtml($this->view->render('_mail_layout.phtml'), 'utf-8');
        $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $this->view->mail_content)), 'utf-8');
        $mail->setSubject($this->view->translate('label_user_register_mail_subject') . ' ' . DOMAIN);
        $mail->addTo($this->_user->email, $this->_user->getName());
        $mail->send(true);
    }

    private function autoLogin()
    {
        $auth = Zend_Auth::getInstance();
        $authAdapter = new $this->_authAdapter(
            $this->_tableName,
            $this->_loginColumn,
            $this->_passwordColumn,
            $this->_skipPass
        );
        $authAdapter->setIdentity($this->_user->getEmail());
        $authAdapter->setCredential($this->_user->getEmail());

        try {
            $result = $auth->authenticate($authAdapter);
        } catch (Zend_Auth_Exception $e) {
            $this->_error('databaseinvalid');

            return false;
        }
        if ($result->isValid() && $auth->hasIdentity()) {
            $resultsObject = $authAdapter->getResultRowObject();
            $auth->getStorage()->write($resultsObject);

            // usunięcie innych
            $sessionOld = Doctrine_Query::create()
                ->from('Session s')
                ->where('s.id_user = ?', $resultsObject->getId())
                ->execute()
            ;
            $sessionOld->delete();

            // zapis zalogowanego usera do bazy
            $session = Session::find(session_id());
            $session->id_user = $resultsObject->getId();
            $session->save();

            return true;
        }
    }
}
