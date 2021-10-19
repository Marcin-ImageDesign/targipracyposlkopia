<?php

class Event_Service_Storage_Adapter_Session extends Event_Service_Storage_Abstract
{
    /**
     * @var Zend_Session_Namespace
     */
    private $_session;

    private $_notificationToUser;

    /**
     * @var array
     */
    private $_elements = [];

    public function __construct()
    {
        $this->_session = new Zend_Session_Namespace('notification');
        $this->_elements = $this->_getElements();
    }

    public function dispatchNotifications($params)
    {
        //lista aktualnych powiadomień
        $notificationsList = $this->getNotifications($params);
        $userNotifications_past = [];
        $toShow = [];
        $ns = $this->_getNamespace();

        $this->_notificationToUser = $this->_elements['ns_notification'];

        if (null !== $this->_notificationToUser && count($this->_notificationToUser) > 0) {
            foreach ($this->_notificationToUser as $pq) {
                $userNotifications_past[] = $pq;
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
        if (!empty($toShow)) {
            foreach ($toShow as $notification) {
                $this->addElement($notification['id_notification']);
            }
        }

        return json_encode(json_encode($toShow));
    }

    private function _getNamespace($id_namespace = null)
    {
        return (null === $id_namespace) ? 'ns_notification' : 'ns_' . $id_namespace;
    }

    private function _getElements()
    {
        return $this->_session->__get('elements');
    }

    private function _setElements($elements)
    {
        return $this->_session->__set('elements', $elements);
    }

    private function addElement($value = null)
    {
        $ns = $this->_getNamespace();
        $this->_elements[$ns][] = $value;
        $this->_setElements($this->_elements);
    }
}
