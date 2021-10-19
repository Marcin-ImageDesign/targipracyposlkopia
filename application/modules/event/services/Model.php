<?php

class Event_Service_Model
{
    /**
     * @var Notification_Service_Storage_Abstract
     */
    private $storage;

    /**
     * @param Notification_Service_Storage_Interface $storage
     */
    public function __construct(Event_Service_Storage_Abstract $storage)
    {
        $this->storage = $storage;
    }

    public function dispatchNotifications($params)
    {
        return $this->storage->dispatchNotifications($params);
    }
}
