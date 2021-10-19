<?php

class LoginRegisterController extends Engine_Controller_Frontend
{
    /**
     * @var string|mixed
     */
    public $redirectUrl;
    /**
     * @var User
     */
    private $_user;

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender() && ($this->_helper->layout->isEnabled() && !mb_strstr($this->_request->getPathInfo(), '/admin'))) {
            $this->renderNewsToPlaceholder('news/_section_nav.phtml', 'section-nav-content');
        }
    }

    public function indexAction()
    {
        // do logowania
        if (mb_strstr($this->_request->getPathInfo(), '/admin')) {
            $this->_helper->layout->setLayout('layout_auth');
        }
        if ($this->hasSelectedEvent()) {
            $loginUrl = $this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()], 'login');
        } else {
            $loginUrl = $this->view->url([], 'home');
        }

        $secure = $this->_request->getPathInfo() !== $loginUrl;
        $anchor = $this->_getParam('anchor', '');
        $redirectParams = $this->_getParam('redirectParams', '');
        if (!empty($redirectParams)) {
            foreach ($redirectParams as $key => $value) {
                $redirectParams[] = $key . '=' . $value;
            }
        }

        $redirect = $this->_getParam('redirect', '') .
            (!empty($redirectParams) ? '?' . join('&', $redirectParams) : '') .
            (!empty($anchor) ? '#' . $anchor : '');

        if ($this->hasSelectedEvent() && empty($redirect)) {
            $redirect = $this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()], 'event_hall');
        }

        $form = new Form_LoginRegister_Login([
            'tableName' => 'User',
            'loginColumn' => 'email',
            'passwordColumn' => 'password',
            'authAdapter' => 'Engine_Auth_Adapter_Doctrine',
        ], $this->getSelectedEvent()->getUri());

        $form->setAction($this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()], 'default_login-register_index') . '?redirect=' . $this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()], 'event_hall'));
        $this->view->form = $form;

        // do rejestracji

        $engineUtils = Engine_Utils::_();
        $this->_user = new User();
        $this->_user->BaseUser = $this->getSelectedBaseUser();
        $this->_user->hash = $engineUtils->getHash();
        $this->_user->id_user_role = UserRole::ROLE_USER;
        $this->_user->is_active = false;

        $formRegister = new User_Form_Register($this->_user);
        $formRegister->setAction($this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()], 'default_login-register_index'));
        $this->view->formRegister = $formRegister;

        if ($this->_request->isPost()) {
            $registerForm = $this->hasParam('UserRegister');
            if ($registerForm) {
                $form = $formRegister;
            }
            if ($form->isValid($this->_request->getPost())) {
                if ($registerForm) {
                    $this->_user->save();
                    //save participation
                    $exhibParticipation = new ExhibParticipation();
                    $exhibParticipation->hash = Engine_Utils::getInstance()->getHash();
                    $exhibParticipation->id_base_user = $this->getSelectedBaseUser();
                    $exhibParticipation->id_user = $this->_user->getId();
                    $exhibParticipation->id_event = $this->getSelectedEvent()->getId();
                    $exhibParticipation->id_exhib_participation_type = ExhibParticipationType::TYPE_PARTICIPANT;
                    $exhibParticipation->is_active = true;
                    $exhibParticipation->save();
                    $this->sendInfoToAdmin();
                    $this->sendActivateLinkToUser();
                    $url = $this->view->url(
                        ['event_uri' => $this->getSelectedEvent()->getUri()],
                        'user_register_thx'
                    );
                    if ($this->displayJsonResponse) {
                        $this->jsonResult['messageType'] = 'html';
                        $this->jsonResult['message'] = $this->view->render('login-register/thx.phtml');
                        $this->jsonResult['content_id'] = $formRegister->getElementsBelongTo() . 'Form';
                        $this->jsonResult['content_remove'] = '.message-error';
                    } else {
                        $this->_redirector->gotoUrlAndExit($url);
                    }
                } elseif (!empty($redirect) && $redirect !== $loginUrl) {
                    $this->setRedirectUrl('/' . $redirect);
                } elseif (!empty($redirect) && $redirect === $loginUrl) {
                    $this->setRedirectUrl('/');
                } elseif ($secure) {
                    $this->setRedirectUrl($this->_request->getRequestUri());
                } else {
                    $this->setRedirectUrl('/' . $redirect);
                }
            } elseif ($this->displayJsonResponse) {
                if ($this->hasParam('UserRegister')) {
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $form->render();
                    $this->jsonResult['content_id'] = 'UserRegisterForm';
                } else {
                    $this->view->displayJsonResponse = $this->displayJsonResponse;
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $form->render();
                    $this->jsonResult['content_id'] = 'LoginForm';
                }
            }
        }

        $this->_redirectIfAuthenticated();
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = '/' . ltrim($url, '/');
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function loginFacebookAction()
    {
        $params = $this->getAllParams();

        // find user in db
        $user = User::findOneByFacebookId($params['id']);
        if (!$user) {
            $user = User::findOneByEmail($params['email']);
        }
        if ($user) {
            if ($user->getFacebookId() !== $params['id']) {
                $user->setFacebookId($params['id']);
                $user->save();
            }
        } else {
            //if user not in db, create one
            $name = explode(' ', $params['name']);
            $user = new User();
            $user->BaseUser = $this->getSelectedBaseUser();
            $user->hash = Engine_Utils::getInstance()->getHash();
            $user->id_user_role = UserRole::ROLE_USER;
            $user->is_active = true;
            $user->facebook_id = $params['id'];
            $user->first_name = $name[0];
            $user->last_name = $name[1];
            $user->email = $params['email'];
            $user->password = Engine_Utils::getInstance()->getHash(); // ??
            $user->save();

            if ($params['picture']['data']['url']
                && ($picture = file_get_contents($params['picture']['data']['url']))) {
                $image = Service_Image::createImage(
                    $user,
                    [
                        'type' => 'image/jpeg',
                        'name' => $user->hash . '.jpg',
                        'data' => $picture, ]
                );

                if ($image->getId() !== 0) {
                    $user->setAvatarImage(true);
                    $user->setIdImage($image->getId());
                    $user->save();
                }
            }
        }
        $this->_saveUserSession($user);

        // redirect
        if ($this->hasSelectedEvent()) {
            $this->setRedirectUrl($this->view->url(['event_uri' => $this->getSelectedEvent()->getUri()], 'event_hall'));
        } else {
            $this->setRedirectUrl($this->view->url([], 'home'));
        }
        $this->_redirector->gotoUrlAndExit($this->getRedirectUrl());
    }

    private function _redirectIfAuthenticated()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            if (!$this->redirectUrl) {
                $this->setRedirectUrl($this->view->url([], 'home'));
            }

            if ($this->displayJsonResponse) {
                $this->jsonResult['result'] = true;
                $this->jsonResult['redirect'] = $this->getRedirectUrl();
            } else {
                $this->_redirector->gotoUrlAndExit($this->getRedirectUrl());
            }
        }
    }

    private function sendInfoToAdmin()
    {
        $smtp_options = $this->getSelectedBaseUser()->getSettingsSmtp();

        $mail = new Engine_Mail($smtp_options);

        $this->view->user = $this->_user;
        $this->view->mail_content = $this->view->render('login-register/_mail-info-admin.phtml');
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
        $this->view->mail_content = $this->view->render('login-register/_mail-activate-link-user.phtml');
        $mail->setBodyHtml($this->view->render('_mail_layout.phtml'), 'utf-8');
        $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $this->view->mail_content)), 'utf-8');
        $mail->setSubject($this->view->translate('label_user_register_mail_subject') . ' ' . DOMAIN);
        $mail->addTo($this->_user->email, $this->_user->getName());
        $mail->send();
    }

    private function _saveUserSession($user)
    {
        // usunicie innych
        $sessionOld = Doctrine_Query::create()
            ->from('Session s')
            ->where('s.id_user = ?', $user->getId())
            ->execute()
        ;
        $sessionOld->delete();

        // zapis zalogowanego usera do bazy
        $session = Session::find(session_id());
        if (!$session) {
            $session = new Session();
        }
        $session->id_user = $user->getId();
        $session->save();
    }
}
