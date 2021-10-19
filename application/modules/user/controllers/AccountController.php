<?php

class User_AccountController extends Engine_Controller_Frontend
{
    public $_user;
    /**
     * @var Event
     */
    private $_event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Send to friend
     */
    private $stf;

    /**
     * @var User_Form_Account
     */
    private $userFormAccount;

    /**
     * @var User_Form_ChangePass
     */
    private $userFormChangePass;

    /**
     * @var user_Form_SendToFriend
     */
    private $userFormSendToFriend;

    /**
     * @var UserConfirmRequest
     */
    private $_userConfirmRequest;

    /**
     * Uczestnictwo o typie Participant.
     *
     * @var ExhibParticipation
     */
    private $_exhibParticipant;

    /**
     * @var NotificationToUser
     */
    private $userNotification;

    public function preDispatch()
    {
        parent::preDispatch();
        if ($this->_helper->layout->isEnabled()) {
            $this->_breadcrumb[] = [
                'url' => $this->view->url(),
                'label' => $this->view->translate('breadcrumb_user_account'),
            ];

            $this->_breadcrumb[] = [
                'url' => $this->view->url(),
                'label' => $this->view->translate($this->addActualBreadcrumb()),
            ];
            $this->_exhibParticipant = $this->getExhibParticipant();
            $this->view->exhibParticipant = $this->_exhibParticipant;
        }
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('user/_section_nav.phtml', 'section-nav-content');
        }
    }

    public function indexAction()
    {
    }

    public function sendToFriendAction()
    {
        $this->stf = new SendToFriend();
        $this->user = $this->userAuth;
        $this->userFormSendToFriend = new User_Form_SendToFriend($this->user, $this->stf);

        if ($this->_request->isPost()) {
            if ($this->userFormSendToFriend->isValid($this->_request->getPost())) {
                // zapis do bazy "kto gdzie kiedy"
                $this->stf->id_user = $this->user->getId();
                $this->stf->date = date('Y-m-d H:i:s');
                $this->stf->save();

                // Przekazywanie zmienny do widoku
                $data = $this->_request->getPost();
                $email = $data['UserSendToFriend']['email'];
                $this->view->email = $email;
                $name = $data['UserSendToFriend']['name'];
                $sender = $data['UserSendToFriend']['sender'];
                $this->view->name = $name;
                //customowa wiadomość:

                $message = $data['UserSendToFriend']['message'];
                $message .= '<br /><br /> Nadawca: ' . $sender;
                $this->view->message = $message;
                $this->view->baseUser = $this->getSelectedBaseUser();

                // wysyłka maila do znajomego
                $smtp_options = $this->getSelectedBaseUser()->getSettingsSmtp();
                $mail = new Engine_Mail($smtp_options);
                $this->view->mail_content = $this->view->render('account/_mail_send-to-friend.phtml');
                $mail->setBodyHtml($this->view->render('_mail_layout.phtml'), 'utf-8');
                $mail->setBodyText(strip_tags(str_replace('<br/>', "\r\n", $this->view->mail_content)), 'utf-8');
                $mail->setSubject($this->view->translate('label_user_account_send-to-friend-email_subject'));
                $mail->addTo($email, $name);
                $mail->send(true);

                if ($this->displayJsonResponse) {
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $this->view->render('account/thx-send-to-friend.phtml');
                    $this->jsonResult['content_id'] = $this->userFormSendToFriend->getElementsBelongTo() . 'Form';
                    $this->jsonResult['result'] = true;
                } else {
                    $this->_flash->success->addMessage($this->view->translate('label_user_register_thx_send-to-friend'));
                    $this->_redirector->gotoRouteAndExit();
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = '' . $this->userFormSendToFriend->render();
                $this->jsonResult['content_id'] = $this->userFormSendToFriend->getElementsBelongTo() . 'Form';
            }
        }
        $this->view->userFormSendToFriend = $this->userFormSendToFriend;
    }

    public function editAction()
    {
        $this->_event = Event::findOneByUri($this->_getParam('event_uri'));
        $shortName = $this->_event->getShortName();

        $this->userFormAccount = new User_Form_Account($this->userAuth, null, $shortName);
        $this->user = $this->userAuth;
        if ($this->_request->isPost()) {
            if ($this->userFormAccount->isValid($this->_request->getPost())) {
                $this->user->save();

                $auth = Zend_Auth::getInstance();
                $auth->getStorage()->write($this->user);

                if (null !== $this->user->getEmailNew()) {
                    $newEmailHash = Engine_Utils::getInstance()->getHash();
                    // kasowanie poprzednich linków
                    $updateLinkQuery = Doctrine_Query::create()
                        ->update('UserConfirmRequest ucr')
                        ->where('ucr.id_user = ?', $this->user->getId())
                        ->addWhere('ucr.is_used = 0')
                        ->addWhere('ucr.type = ?', UserConfirmRequest::EMAIL_ACTIVATION)
                        ->set('ucr.is_used', '?', 1)
                    ;

                    $updateLinkQuery->execute();

                    // Tworzenie nowego "linku"
                    $userConfirmRequest = new UserConfirmRequest();
                    $userConfirmRequest->hash = $newEmailHash;
                    $userConfirmRequest->id_user = $this->user->getId();
                    $userConfirmRequest->setType(UserConfirmRequest::EMAIL_ACTIVATION);
                    $userConfirmRequest->save();

                    // Przekazywanie zmienny do widoku
                    $this->view->user = $this->user;
                    $this->view->newEmailHash = $newEmailHash;
                    $this->view->baseUser = $this->getSelectedBaseUser();
                    $this->view->uri = DOMAIN . $this->view->url(['hash' => $userConfirmRequest->getHash()], 'user_account_change-email');

                    // wysyłka
                    $emailSettings = $this->getSelectedBaseUser()->getSettings('smtp');
                    $mail = new Engine_Mail($emailSettings);
                    $mail->setBodyHtml($this->view->render('account/_mail_change-email.phtml'));
//                    $mail->setDefaultRecipients();
                    $mail->addTo($this->user->getEmailNew(), $this->user->getName());
                    $mail->setSubject($this->view->translate('Change email'));

                    $mail->send();
                }

                if (isset($_FILES['img']) && 0 === $_FILES['img']['error']) {
                    $image = Service_Image::createImage(
                        $this->user,
                        [
                            'type' => $_FILES['img']['type'],
                            'name' => $_FILES['img']['name'],
                            'source' => $_FILES['img']['tmp_name'], ]
                    );

                    $this->user->setAvatarImage(true);
                    $this->user->setIdImage($image->getId());
                    $this->user->save();
                }

                if ($this->displayJsonResponse) {
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $this->view->render('account/thx-edit.phtml');
                    $this->jsonResult['content_id'] = $this->userFormAccount->getElementsBelongTo() . 'Form';
                    $this->jsonResult['result'] = true;
                } else {
                    $this->_flash->success->addMessage($this->view->translate('Save successfully completed'));
                    $this->_redirector->gotoRouteAndExit();
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = '' . $this->userFormAccount->render();
                $this->jsonResult['content_id'] = $this->userFormAccount->getElementsBelongTo() . 'Form';
            }
        }

        $userArray = $this->userAuth->toArray();
        //var_dump($userArray);
        //$userArray['company_phone'] = '555-555-555';
        $userArray['company_post_code'] = $userArray['post_code'];
        $userArray['company_city'] = $userArray['city'];
        $this->userFormAccount->populate($userArray);
        $this->view->userFormAccount = $this->userFormAccount;
    }

    public function thxEditAction()
    {
    }

    public function changePassAction()
    {
        $this->user = $this->userAuth;
        $this->userFormChangePass = new User_Form_ChangePass($this->user);

        if ($this->_request->isPost()) {
            if ($this->userFormChangePass->isValid($this->_request->getPost())) {
                $this->user->save();

                $auth = Zend_Auth::getInstance();
                $auth->getStorage()->write($this->user);

                if ($this->displayJsonResponse) {
                    $this->jsonResult['messageType'] = 'html';
                    $this->jsonResult['message'] = $this->view->render('account/thx-change-pass.phtml');
                    $this->jsonResult['content_id'] = $this->userFormChangePass->getElementsBelongTo() . 'Form';
                    $this->jsonResult['result'] = true;
                } else {
                    $this->_flash->addMessage('Save successfully completed');
                    $this->_redirector->gotoRouteAndExit();
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $this->userFormChangePass->render();
                $this->jsonResult['content_id'] = $this->userFormChangePass->getElementsBelongTo() . 'Form';
            }
        }

        $this->view->userFormChangePass = $this->userFormChangePass;
    }

    public function thxChangePassAction()
    {
    }

    public function changeEmailAction()
    {
        $newEmailHash = $this->_getParam('hash');
        $this->_userConfirmRequest = UserConfirmRequest::findOneByHashAndType($newEmailHash, UserConfirmRequest::EMAIL_ACTIVATION);

        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_userConfirmRequest, 'UserConfirmRequest Not Found hash: (' . $this->_getParam('userConfirmRequestHash') . ')');

        $this->user = $this->_userConfirmRequest->User;

        if (($this->user && !$this->user->is_banned) && $this->_userConfirmRequest) {
            if ($this->_userConfirmRequest->is_used) {
                $message = $this->view->translate('label_user_user_activate-email_link-inactive');
            } else {
                $this->user->setEmail($this->user->getEmailNew());
                $this->user->setEmailNew(null);
                $this->user->save();

                // uzupełnienie userConfirmRequest
                $used_at = new Zend_Date();
                $used_at = $used_at->toString('YYYY-MM-dd HH:mm:ss');
                $this->_userConfirmRequest->setIsUsed(true);
                $this->_userConfirmRequest->setUsedAt($used_at);
                $this->_userConfirmRequest->save();

                $auth = Zend_Auth::getInstance();
                $auth->getStorage()->write($this->user);

                $message = $this->view->translate('label_user_account_activate_account_ok');
            }
        } elseif ($this->_user->is_banned) {
            $message = $this->view->translate('label_user_account_change-email_user-banned');
        }

        $this->view->message = $message;
    }

    public function rankAction()
    {
        $eventSite = EventSite::findOneByUri('gamification_rules', $this->getSelectedLanguage(), $this->getSelectedEvent()->getId());
        $this->forward404Unless($eventSite, 'Stona nie istnieje (' . $this->_getParam('site_uri') . ')');

        $this->view->eventSite = $eventSite;
    }

    public function yourPlaceAction()
    {
        $this->getMyPlace();
    }

    public function topTenAction()
    {
        $this->_event = Event::findOneByUri($this->_getParam('event_uri'));

        // top 10
        $top10ListQuery = Doctrine_Query::create()
            ->from('GamificationUserPoints gup')
            ->leftJoin('gup.User u')
            ->leftJoin('gup.GamificationUserHistoryPoints guhp WITH guhp.id_event = ?', $this->_event->getId())
            ->addWhere('u.is_banned = 0')
            ->orderBy('points DESC, id_user ASC')
            ->limit(10)
        ;
        $this->view->top10List = $top10ListQuery->execute();
    }

    public function dayRankingAction()
    {
        $this->_event = Event::findOneByUri($this->_getParam('event_uri'));

        // Ranking dzienny
        $dayRankingQuery = Doctrine_Query::create()
            ->from('GamificationDayRanking gup')
            ->leftJoin('gup.User u')
            ->addWhere('gup.id_event = ?', $this->_event->getId())
            ->addWhere('u.is_banned = 0')
            ->orderBy('points DESC, id_user ASC')
        ;

        //paginacja
        $pager = new Doctrine_Pager($dayRankingQuery, $this->_getParam('page', 1), 10);
        $dayRankingList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->dayRankingList = $dayRankingList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;

        //$this->view->dayRankingList =  $dayRankingQuery->execute();
    }

    public function notificationsHistoryAction()
    {
        $filter = '';
        $filter = $this->_getParam('filter');

        $query = Doctrine_Query::create()
            ->from('NotificationToUser ntu')
            ->leftJoin('ntu.Notifications n')
            ->where('ntu.id_exhib_participation = ?', $this->_exhibParticipant->getId())
            ->addWhere('ntu.visible_in_history = 1')
            ->orderBy('n.notification_date desc')
        ;

        if ($filter && 'today' === $filter) {
            $query->addWhere('n.notification_date >= ? and n.notification_date <= ?', [date('Y-m-d 00:00'), date('Y-m-d 23:59')]);
        }
        if ($filter && 'yesterday' === $filter) {
            $query->addWhere('n.notification_date >= ? and n.notification_date <= ?', [date('Y-m-d 00:00', strtotime('yesterday')), date('Y-m-d 23:59', strtotime('yesterday'))]);
        }

        //paginacja
        $pager = new Doctrine_Pager($query, $this->_getParam('page', 1), 10);
        $result = $pager->execute();

        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;

        $this->view->notificationHistory = $result;
        $this->view->filter = $filter;
    }

    public function notificationDeleteAction()
    {
        $this->userNotification = NotificationToUser::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->userNotification, 'Notification DON\'T Exists (' . $this->_getParam('hash') . ')');

        $this->userNotification->delete();

        $this->_flash->succes->addMessage('Powiadomienie zostało usunięte');
        $this->_redirector->gotoRouteAndExit([], 'user_account_notifications-history');
    }

    private function getMyPlace()
    {
        $this->_event = Event::findOneByUri($this->_getParam('event_uri'));
        $this->user = $this->userAuth;

        // punkty użytkownika i ranking
        $userRankQuery = Doctrine_Query::create()
            ->select('points')
            ->from('GamificationUserPoints gup')
            ->where('gup.id_event= ?', $this->_event->getId())
            ->addWhere('gup.id_user= ?', $this->user->getId())
        ;
        $userRank = $userRankQuery->fetchOne();
        $this->view->userRank = $userRank;

        // jeżeli nie ma ilości punktów to wyświetl info że user nie bierze udziału w rankingu
        if (empty($userRank->points)) {
            $this->view->noUser = true;

            return true;
        }

        //zwraca obecną pozycję na liście danego usera
        $position = GamificationUserPoints::getUserRank($this->getSelectedEvent()->getId(), $this->user->getId());
        $position = (int) $position['rank'];
        $this->view->position = $position;

        $limit = 10;
        $offset = $position - 5;
        if ($offset < 0) {
            $offset = 0;
        }

        // odczyt ile punktów trzeba uzyskać aby awansować w rankingu
        $countUp = GamificationUserPoints::getUserCountUp($this->getSelectedEvent()->getId(), $userRank->points);
        $this->view->countUp = $countUp;

        // odczyt poprzedniej pozycji usera na liście uczestników
        $lastPositionQuery = Doctrine_Query::create()
            ->select('rank')
            ->from('GamificationUserHistoryPoints guhp')
            ->where('guhp.id_event= ?', $this->_event->getId())
            ->addWhere('guhp.id_user= ?', $this->user->getId())
        ;
        $lastPosition = $lastPositionQuery->fetchOne();

        //obliczam trend użytkownika
        if ($lastPosition && $position < $lastPosition->rank) {
            $this->view->trendUser = 'up';
        } elseif ($lastPosition && $position > $lastPosition->rank) {
            $this->view->trendUser = 'down';
        } elseif ($lastPosition) {
            $this->view->trendUser = 'equal';
        } else {
            $this->view->trendUser = 'up';
        }

        // liczba wszystkich uczestników biorących udział w konkursie w danym evencie
        $countAllQuery = Doctrine_Query::create()
            ->select('count(*) as count')
            ->from('GamificationUserPoints gup')
            ->where('gup.id_event= ?', $this->_event->getId())
            ->limit(1)
        ;
        $countAll = $countAllQuery->execute()->getFirst();
        $this->view->countAll = $countAll->count;

        // oblicza pozostałe dni eventu
        $this->view->endDays = round(((strtotime($this->_event->getDateEnd()) - strtotime(date('Y-m-d'))) / 86400));

        // oblicza ile punktów dodano w dniu dzisiejszym
        $this->view->pointsToday = GamificationUserPoints::getUserPointsToday($this->_event->getId(), $this->user->getId());

        // Your place
        $yourPlaceListQuery = Doctrine_Query::create()
            ->from('GamificationUserPoints gup')
            ->leftJoin('gup.User u')
            ->leftJoin('gup.GamificationUserHistoryPoints guhp WITH guhp.id_event = ?', $this->_event->getId())
            ->addWhere('u.is_banned = 0')
            ->orderBy('points DESC, id_user ASC')
            ->limit($limit)
            ->offset($offset)
        ;
        $this->view->yourPlaceList = $yourPlaceListQuery->execute();

        $this->view->selectedEvent = $this->getSelectedEvent();
    }
}
