<?php

class Event_AdminNotificationsController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Admin_Notification_Filter|mixed
     */
    public $filter;
    /**
     * @var \Event_Form_Admin_Notification|mixed
     */
    public $_notificationForm;
    /**
     * @var Event
     */
    private $_event;

    /**
     * @var Notification
     */
    private $_notification;

    public function postDispatch()
    {
        parent::postDispatch();

        $this->view->show_gamification = Engine_Variable::getInstance()->getVariable(Variable::SHOW_GAMIFICATION);

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-notifications/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        if (!$this->getSelectedEvent()) {
            $url = $this->view->url([], 'admin_select-event') . '?return=' . $this->view->url();
            $this->_redirector->gotoUrlAndExit($url);
        }
        $this->_event = $this->getSelectedEvent();

        $search = ['notification_date_from' => '', 'notification_date_to' => ''];

        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('notifications_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->notifications_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->notifications_filter->{$key}) ? $this->_session->notifications_filter->{$key} : $value);
            }
        }

        $this->filter = new Event_Form_Admin_Notification_Filter();
        $this->filter->populate($search);

        $notifications_query = Doctrine_Query::create()
            ->from('Notifications n')
        ;
        if (!empty($search['notification_date_from'])) {
            $notifications_query->addWhere('notification_date >= ?', $search['notification_date_from'] . ' 00:00');
        }
        if (!empty($search['notification_date_to'])) {
            $notifications_query->addWhere('notification_date <= ?', $search['notification_date_to'] . ' 23:59');
        }

        $notifications_query->addWhere('id_event = ?', $this->getSelectedEvent()->getId());
        $notifications_query->orderBy('notification_date desc');

        $pager = new Doctrine_Pager($notifications_query, $this->_getParam('page', 1), 20);
        $notification_list = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->notification_list = $notification_list;
        $this->view->filter = $this->filter;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Notifications list'));
    }

    public function newAction()
    {
        $this->_notification = new Notifications();
        $this->_notification->Event = $this->getSelectedEvent();

        $this->_notification->id_event = $this->_notification->Event->getId();
        $this->_notification->hash = $this->engineUtils->getHash();
        $this->_notificationForm = new Event_Form_Admin_Notification($this->_notification);
        $this->notificationForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_notification-new'));
    }

    public function editAction()
    {
        $this->_notification = Notifications::findOneByHash($this->_getParam('hash'));

        $this->forward404Unless($this->_notification, 'Notification not found, hash: (' . $this->_getParam('hash') . ')');
        $this->_notificationForm = new Event_Form_Admin_Notification($this->_notification);

        $this->notificationForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_notification-edit'));
    }

    public function deleteAction()
    {
        $this->_notification = Notifications::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->_notification, 'Notification not found, hash: (' . $this->_getParam('hash') . ')');

        $this->_notification->delete();

        $this->_flash->succes->addMessage('Item has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'admin_event-notifications');
    }

    private function notificationForm()
    {
        if ($this->_request->isPost() && $this->_notificationForm->isValid($this->_request->getPost())) {
            $this->_notification->save();
            $this->_flash->succes->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit(['hash' => $this->_notification->getHash()], 'admin_event-notification_edit');
        }

        $this->_helper->viewRenderer('admin-notifications/edit', null, true);
        $this->view->notificationForm = $this->_notificationForm;
    }
}
