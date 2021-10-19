<?php

class Event_Service_Storage_Adapter_Database extends Event_Service_Storage_Abstract
{
    /**
     * @var User
     */
    private $userAuth = false;

    private $_notificationToUser;

    private $_notification;

    private $engineUtils;

    public function __construct()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->engineUtils = Engine_Utils::_();
    }

    public function dispatchNotifications($params)
    {
        //tylko dla zalogowanych uczestników (wystawcy, admini nie widzą)
        if ($params['id_exhib_participation']) {
            //lista aktualnych powiadomień
            $notificationsList = $this->getNotifications($params);
            $userNotifications_past = [];
            $toShow = [];

            $cache = Zend_Registry::get('Zend_Cache');
            $cache_name = 'notification_to_user_event_' . $params['id_event'];

            $this->_notificationToUser = $cache->load($cache_name);

            if (false === $this->_notificationToUser) {
                $this->_notificationToUser = Doctrine::getTable('NotificationToUser')->findAll(Doctrine::HYDRATE_ARRAY);
                $cache->save($this->_notificationToUser, $cache_name);
            }
            //sprawdzamy, które już widział
            foreach ($this->_notificationToUser as $pq) {
                if ($pq['id_exhib_participation'] === $params['id_exhib_participation']) {
                    $userNotifications_past[] = $pq['id_notification'];
                }
            }

            if (!empty($userNotifications_past)) {
                foreach ($notificationsList as $not) {
                    if (!in_array($not['id_notification'], $userNotifications_past, true)) {
                        $toShow[] = $not;
                    }
                }
            } else {
                $toShow = $notificationsList;
            }
            //zapisujemy nowo pokazane do historii
            if (!empty($toShow)) {
                foreach ($toShow as $notification) {
                    $this->_notification = new NotificationToUser();
                    $this->_notification->hash = $this->engineUtils->getHash();
                    $this->_notification->id_notification = $notification['id_notification'];
                    $this->_notification->id_exhib_participation = $params['id_exhib_participation'];
                    $this->_notification->save();
                }
                //odświeżamy cache
                $cache->save(Doctrine::getTable('NotificationToUser')->findAll(Doctrine::HYDRATE_ARRAY), $cache_name);
            }
        } else {
            $toShow = null;
        }

        //zwracamy powiadomienia do pokazania
        return json_encode(json_encode($toShow));
    }
}
