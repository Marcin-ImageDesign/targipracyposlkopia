<?php

class Statistics_Service_Manager
{
    /**
     * @param string     $channel
     * @param interger   $id_element
     * @param array      $data
     * @param mixed      $id_event
     * @param null|mixed $id_element_item
     * @param null|mixed $id_user_new
     *
     * @return Statistics
     */
    public static function add($channel, $id_element, $id_event, $data = [], $id_element_item = null, $id_user_new = null)
    {
        $id_base_user = null;
        if (Zend_Registry::isRegistered('BaseUser')) {
            $id_base_user = Zend_Registry::get('BaseUser')->getId();
        }

        // sprawdzamy czy event jest archiwalny -> jeśli tak to nie zapisujemy statystyk
        if (Statistics_Service_Manager::checkEventNoActive($id_event)) {
            return;
        }
        $id_user = null;
        if (null !== ($user = Zend_Auth::getInstance()->getIdentity())) {
            $id_user = $user->getId();
        }

        if ($id_user_new) {
            $id_user = $id_user_new;
        }

        $statisics = new Statistics();
        $statisics->id_base_user = $id_base_user;
        $statisics->ip = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_ADDR');
        $statisics->channel = $channel;
        $statisics->id_user = $id_user;
        $statisics->id_element = $id_element;
        $statisics->id_element_item = $id_element_item;
        $statisics->id_event = $id_event;
        $statisics->data = Zend_Json::encode($data);
        $statisics->save();

        return $statisics;
    }

    // Funkcja sprawdza czy dany event jest archiwalny
    public static function checkEventNoActive($id_event)
    {
        $eventActive = Doctrine_Query::create()
            ->select('e.id_event')
            ->from('Event e')
            ->addWhere('id_event = ?', $id_event)
            ->addWhere('is_archive = 1')
            ->execute()->getFirst();

        if ($eventActive) {
            return 1;
        }

        return 0;
    }

    public static function checkIfAllowed($channel, $id_event, $delay)
    {
        $id_user = null;
        if (null !== ($user = Zend_Auth::getInstance()->getIdentity())) {
            $id_user = $user->getId();
        } else {
            return true;
        }

        $lastApply = Doctrine_Query::create()
            ->select('s.created_at')
            ->from('Statistics s')
            ->addWhere('id_event = ?', [$id_event])
            ->addWhere('id_user = ?', [$id_user])
            ->addWhere('channel = ? ', [$channel])
            ->orderBy('s.created_at DESC')
            ->limit(1)
            ->execute()->getFirst();
        if ($lastApply) {
            $nextPointsAt = strtotime($lastApply->created_at . '+ ' . $delay . ' seconds');

            if ($nextPointsAt > strtotime('now')) {
                return false;
            }
        }

        return true;
    }
}
