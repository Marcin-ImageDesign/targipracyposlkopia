<?php

class AuthController extends Engine_Controller_Frontend
{
    /**
     * @var string|mixed
     */
    public $redirectUrl;

    /**
     * @var UserConfirmRequest|mixed
     */
    public $_userConfirmRequest;

    /**
     * @var User|mixed
     */
    public $_user;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->renderNewsToPlaceholder('news/_section_nav.phtml', 'section-nav-content');
        }
    }

    public function indexAction()
    {
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

        $form = new Form_Login([
            'tableName' => 'User',
            'loginColumn' => 'email',
            'passwordColumn' => 'password',
            'authAdapter' => 'Engine_Auth_Adapter_Doctrine',
        ]);

        $this->view->form = $form;
        if ($this->_request->isPost()) {
            $data = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);

            if ($form->isValid($data)) {
                if (!empty($redirect) && $redirect !== $loginUrl) {
                    $this->setRedirectUrl('/' . $redirect);
                } elseif (!empty($redirect) && $redirect === $loginUrl) {
                    $this->setRedirectUrl('/');
                } elseif ($secure) {
                    $this->setRedirectUrl($this->_request->getRequestUri());
                } else {
                    $this->setRedirectUrl('/' . $redirect);
                }

                if (empty($_POST)) {
                    $this->_helper->json([
                        'session_id' => session_id(),
                    ]);

                    return;
                }
            } elseif ($this->displayJsonResponse) {
                $this->view->displayJsonResponse = $this->displayJsonResponse;
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $form->render();
                $this->jsonResult['content_id'] = $form->getElementsBelongTo() . 'Form';
            }

            if (empty($_POST)) {
                $this->_helper->json([
                    'messages' => $form->getMessages(),
                ]);

                return;
            }
        }

        $this->_redirectIfAuthenticated();
        $this->view
            ->placeholder('h1_content')
            ->set($this->view->translate('Login'));
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();

        $sessionDb = Session::find(session_id());
        if ($sessionDb) {
            $sessionDb->id_user = null;
            $sessionDb->save();
        }
        $this->_session->unsetAll();

        if (!$this->getParam('json', false) ) {
            $this->redirect(  $this->_getParam('redirect', '/'));
            exit();
        }

        $this->_helper->json([
            'success' => true,
        ]);

        return;
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = '/' . ltrim($url, '/');
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function recoverPasswordAction()
    {
        $form = new Form_RecoveryEmail();
        $this->view->form = $form;

//        $form->populate($this->_getAllParams());
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_getAllParams())) {
                $email = $form->email->getValue();
                $user = Doctrine_Query::create()
                    ->from('User u')
                    ->where('u.email = ?', $email)
                    ->andWhere('u.id_base_user = ?', $this->getSelectedBaseUser()->getId())
                    ->execute()
                    ->getFirst();

                if ($user) {
                    $recoverHash = Engine_Utils::getInstance()->getHash();
                    $zDate = new Zend_Date(null);
                    $recoverEmailTime = $zDate->toString('Y-M-d H:m:s');
                    $zDate->addDay(1);

                    // kasowanie poprzednich linków
                    $updateLinkQuery = Doctrine_Query::create()
                        ->update('UserConfirmRequest ucr')
                        ->where('ucr.id_user = ?', $user->getId())
                        ->addWhere('ucr.is_used = 0')
                        ->addWhere('ucr.type = ?', UserConfirmRequest::CHANGE_PASSWORD)
                        ->set('ucr.is_used', '?', 1);

                    $updateLinkQuery->execute();

                    // Tworzenie nowego "linku"
                    $userConfirmRequest = new UserConfirmRequest();
                    $userConfirmRequest->hash = $recoverHash;
                    $userConfirmRequest->id_user = $user->getId();
                    $userConfirmRequest->setType(UserConfirmRequest::CHANGE_PASSWORD);
                    $userConfirmRequest->save();

                    $this->view->user = $user;
                    $this->view->recoverHash = $recoverHash;
                    $this->view->baseUser = $this->getSelectedBaseUser();
                    $this->view->uri = DOMAIN . $this->view->url(['hash' => $userConfirmRequest->getHash(), 'event_uri' => $this->getSelectedEvent()->getUri()], 'password_recover_hash');

                    $emailSettings = $this->getSelectedBaseUser()->getSettings('smtp');
                    $mail = new Engine_Mail($emailSettings);
                    $mail->setBodyHtml($this->view->render('auth/_mail_recovery.phtml'));
//                $mail->setDefaultRecipients();
                    $mail->addTo($user->getEmail());
                    $mail->setSubject($this->view->translate('Password recovery'));
                    $mail->send();

                    if ($this->displayJsonResponse) {
                        $this->jsonResult['messageType'] = 'html';
                        $this->jsonResult['message'] = $this->view->render('auth/send.phtml');
                        $this->jsonResult['content_id'] = 'RecoveryPass';
                        $this->jsonResult['result'] = true;
                    } else {
                        $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'password-recovery_thx');
                    }
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $form->render();
                $this->jsonResult['content_id'] = 'RecoveryPass';
            }
        }
    }

    public function sendAction()
    {
    }

    public function passwordResetAction()
    {
        $recoveryHash = $this->_getParam('hash');
        if (null === $recoveryHash) {
            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'password_recover_validity');
        }

        $this->_userConfirmRequest = UserConfirmRequest::findOneByHashAndType(
            $recoveryHash,
            UserConfirmRequest::CHANGE_PASSWORD
        );

        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_userConfirmRequest, 'UserConfirmRequest Not Found hash: (' . $this->_getParam('userConfirmRequestHash') . ')');

        $created_at = new Zend_Date($this->_userConfirmRequest->created_at, null, new Zend_Locale('pl'));
        $end_date = $created_at->addDay(1);
        $now = Zend_Date::now();
        if ($now->isEarlier($end_date)) {
            $this->_user = $user = $this->_userConfirmRequest->User;
            $message = $this->view->translate('label_default_auth_password-reset-error');

            if (($this->_user && !$this->_user->is_banned) && $this->_userConfirmRequest) {
                if ($this->_userConfirmRequest->is_used) {
                    $translate = $this->view->translate('label_user_user_change-pass_link-inactive');
                    $this->view->errorMessage = $translate;
                } else {
                    $form = new Form_ChangePass($this->_user);

                    $form->populate($this->_getAllParams());
                    if ($form->getValue('password') && $form->isValid($this->_getAllParams())) {
                        $user->password = $this->_user->getHashPassword($form->password->getValue());
                        $user->save();
                        // uzupełnienie userConfirmRequest
                        $used_at = new Zend_Date();
                        $used_at = $used_at->toString('YYYY-MM-dd HH:mm:ss');
                        $this->_userConfirmRequest->setIsUsed(true);
                        $this->_userConfirmRequest->setUsedAt($used_at);
                        $this->_userConfirmRequest->save();
                        $auth = Zend_Auth::getInstance();
                        $auth->getStorage()->write($user);
                        $this->_flash->success->addMessage($this->view->translate('Password succefully changed'));
                        if ($this->getSelectedEvent()) {
                            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'user_account_edit');
                        } else {
                            $this->_redirector->gotoRouteAndExit([], 'home');
                        }
                    }
                    $this->view->form = $form;
                }
            } else {
                $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'password_recover_validity');
            }
        } else {
            $translate = $this->view->translate('label_password_reset_error-later');
            $this->view->errorMessage = $translate;
        }
    }

    public function invalidHashAction()
    {
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
}
