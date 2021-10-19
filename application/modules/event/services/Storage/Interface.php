<?php

interface Event_Service_Storage_Interface
{
    public function getNotifications($params);

    public function dispatchNotifications($params);
}
