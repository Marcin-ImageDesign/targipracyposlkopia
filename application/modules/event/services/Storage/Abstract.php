<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 03.10.13
 * Time: 08:49
 * To change this template use File | Settings | File Templates.
 */
abstract class Event_Service_Storage_Abstract implements Event_Service_Storage_Interface
{
    public function getNotifications($params)
    {
        return Notifications::getNotifications($params);
    }
}
