<?php

/**
 * Notifications.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Notifications extends Table_Notifications
{
    public static function getNotifications($params = [])
    {
        $date_from = isset($params['date_from']) && self::validateDate($params['date_from']) ? $params['date_from'] : false;
        $date_to = isset($params['date_to']) && self::validateDate($params['date_to']) ? $params['date_to'] : false;

        $query = Doctrine_Query::create()
            ->from('Notifications n')
        ;
        if ($date_from) {
            $query->addWhere('notification_date >= ?', $date_from);
        }
        if ($date_to) {
            $query->addWhere('notification_date <= ?', $date_to);
        }

        $query->addWhere('id_event = ?', $params['id_event']);
        $query->orderBy('notification_date desc');

        return $query->execute([], Doctrine::HYDRATE_ARRAY);
    }

    public function getDateFormat($format = 'Y-m-d H:i')
    {
        return date($format, strtotime(!empty($this->notification_date) ? $this->notification_date : date('Y-m-d H:i')));
    }

    private static function validateDate($date, $format = 'Y-m-d H:i')
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) === $date;
    }
}
