<?php

class Inquiry_Service_Manager
{
    /**
     * @param string     $channel
     * @param string     $subject
     * @param array      $data
     * @param null|int   $idBaseUser
     * @param null|mixed $id_exhib_stand
     *
     * @return Inquiry
     */
    public static function add($channel, $subject, $data, $idBaseUser = null, $id_exhib_stand = null)
    {
        $data = (array) $data;

        $data['IP address'] = Engine_Utils::getInstance()->getIpAddress();

        $item = new Inquiry();
        $item->channel = $channel . '';
        $item->subject = $subject . '';
        $item->hash = Engine_Utils::_()->getHash();
        $item->data = Zend_Json::encode($data);

        if (null === $idBaseUser) {
            $idBaseUser = Zend_Registry::get('BaseUser')->getId();
        }

        $item->id_base_user = (int) $idBaseUser;
        $item->id_exhib_stand = $id_exhib_stand;

        $item->save();

        Service_EventManager::trigger('inquiryCreate', ['object' => $item]);

        return $item;
    }

    public static function delete(Inquiry $item)
    {
        Service_EventManager::trigger('inquiryDelete', ['object' => $item]);
        $item->delete();
    }
}
