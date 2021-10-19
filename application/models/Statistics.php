<?php

/**
 * Statistics.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Statistics extends Table_Statistics
{
    const CHANNEL_HALL_VIEW = 'hall_view'; // wejście na halę
    const CHANNEL_STAND_VIEW = 'stand_view'; // wejście na stoisko
    const CHANNEL_STAND_PRODUCT_VIEW = 'stand_product_view'; // Wyświetlenie oferty na stoisku(produktu)
    const CHANNEL_STAND_PRODUCT_VIEWLIST = 'stand_product_viewlist'; // stoisko - lista produktów
    const CHANNEL_STAND_PRODUCT_APPLY = 'stand_product_apply'; // stoisko - aplikowanie na ofertę
    const CHANNEL_STAND_CONTACT_VIEW = 'stand_contact_view'; // stoisko - kontakt
    const CHANNEL_STAND_FILE_VIEW = 'stand_file_view'; // stoisko - pobranie pliku
    const CHANNEL_STAND_FACEBOOK_VIEW = 'stand_facebook_view'; // stoisko - facebook
    const CHANNEL_STAND_SKYPE_CLICK = 'stand_skype_click'; // stoisko - skype
    const CHANNEL_STAND_SHOP_VIEW = 'stand_shop_view'; // stoisko - wejście w link sklepu produktu
    const CHANNEL_STAND_WWW_VIEW = 'stand_www_view'; // stoisko - wyjscie na zewnetrzna strone www
    const CHANNEL_STAND_VIDEO_VIEW = 'stand_video_view'; // wyświetlenie filmu video na stoisku

    const CHANNEL_RECEPTION_WEBINAR = 'reception_webinar'; // recepcja - webinaria
    const CHANNEL_RECEPTION_VIEW = 'reception_view'; // recepcja - wejście
    const CHANNEL_RECEPTION_FILE_VIEW = 'reception_file_view'; // recepcja - pobranie pliku
    const CHANNEL_RECEPTION_VIDEO_VIEW = 'reception_video_view'; // recepcja - wyświetlenie filmu

    const CHANNEL_ACCOUNT_INVITATION = 'account_invitation'; // wysłanie zaproszenia do znajomego
    const CHANNEL_ACCOUNT_REGISTRATION_WITH_INVITATION = 'account_registration_with_invitation'; // rejestracja z zaproszenia
    const CHANNEL_ACCOUNT_REGISTRATION_EXTENDED = 'account_registration_extended'; // punkty za rozszerzoną rejestrację
    const CHANNEL_ACCOUNT_REGISTRATION = 'account_registration'; // punkty za rejestrację

    const CHANNEL_STAND_FACEBOOK_SHARE = 'stand_facebook_share'; // stoisko - udostępnienie stoiska na FB
    const CHANNEL_STAND_GOOGLEPLUS_SHARE = 'stand_googleplus_share'; // stoisko - udostępnienie stoiska na GOOGLE+
    const CHANNEL_STAND_TWITTER_SHARE = 'stand_twitter_share'; // stoisko - udostępnienie stoiska na TWITTERZE

    const CHANNEL_EVENT_FACEBOOK_SHARE = 'event_facebook_share'; // event - udostępnienie stoiska na FB

    public static function getStatisticsHistory($channel, $date_from, $date_to, $id_event = null, $id_element = null)
    {
        $query = Doctrine_Query::create()
            ->select('s.channel, UNIX_TIMESTAMP(date_format(s.created_at,\'%Y-%m-%d %H:%i:00.0\')) as hit, count(*) as cnt')
            ->from('Statistics s')
            ->where(' s.channel = ? ', [$channel])
            ->groupBy(' s.channel, date_format(s.created_at,\'%Y-%m-%d %H:%i:00.0\')')
        ;

        if (!empty($id_event)) {
            $query->addWhere('s.id_event = ?', [$id_event]);
        }
        if (!empty($id_element)) {
            $query->addWhere('s.id_element IN ?', [$id_element]);
        }
        if (!empty($date_from)) {
            $query->addWhere('s.created_at > ?', [$date_from . ' 00:00:00']);
        }
        if (!empty($date_to)) {
            $query->addWhere('s.created_at < ?', [$date_to . ' 23:59:59']);
        }

        $results = $query->execute([], Doctrine_Core::HYDRATE_ARRAY);

        if (!empty($results)) {
            $ret = [];
            foreach ($results as $val) {
                $ret[$val['hit']][$val['channel']] = $val['cnt'];
            }

            return $ret;
        }

        return false;
    }

    public static function checkEntry($channel, $id_element, $id_event, $id_user_new = null)
    {
        // połączenie
        $conn = Doctrine_Manager::connection();

        $id_user = null;
        if (null !== ($user = Zend_Auth::getInstance()->getIdentity())) {
            $id_user = $user->getId();
        }

        if ($id_user_new) {
            $id_user = $id_user_new;
        }

        $query = "
        SELECT id_statistics
        FROM statistics
        WHERE channel =  '" . $channel . "'
        AND id_event =  '" . $id_event . "'
        AND id_element =  '" . $id_element . "'
        AND id_user =  '" . $id_user . "'
        LIMIT 1
        ";

        $entryList = $conn->prepare($query);
        $entryList->execute();
        $entry = $entryList->fetch();

        return $entry ? true : false;
    }

    public static function delEntry($channel, $id_element, $id_event, $id_user_new = null)
    {
        // połączenie
        $conn = Doctrine_Manager::connection();

        $id_user = null;
        if (null !== ($user = Zend_Auth::getInstance()->getIdentity())) {
            $id_user = $user->getId();
        }

        if ($id_user_new) {
            $id_user = $id_user_new;
        }

        // kasowanie całego rankingu dotyczącego aktualnego wydarzenia
        $conn->prepare('DELETE FROM statistics WHERE id_event=' . $id_event . ' AND id_element=' . $id_element . ' AND id_user=' . $id_user . ' AND channel = "' . $channel . '"')->execute();
    }
}
