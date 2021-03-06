<?php

/**
 * Variable.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Variable extends Table_Variable
{
    /**
     * Czy wysyłka wiadomości email jest włączona.
     */
    const EMAIL_SEND_ON = 'email_send_on';

    /**
     * Czy wysyłając korzystamy z serwera SMTP.
     */
    const EMAIL_SMTP_ON = 'email_smtp_on';

    /**
     * Czy wysyłkę wiadomości wykonuje cron
     * 0 - nie, wiedomości sa od razu wysyłane
     * 1 - tak, wiadomość trafia do kolejki, którą cyklicznie wysyła cron.
     */
    const EMAIL_CRON_ON = 'email_cron_on';

    /**
     * Adres serwera SMTP.
     */
    const EMAIL_TRANSPORTER = 'email_transporter';

    /**
     * Nazwa/login użytkownika do konta email.
     */
    const EMAIL_USER = 'email_user';

    /**
     * Hasło do konta email.
     */
    const EMAIL_PASSWORD = 'email_password';

    /**
     * Adres e-mail nadawcy.
     */
    const EMAIL_FROM = 'email_from';

    /**
     * Adres e-mail odbiorcy, np: powiadomienia dla administratora.
     */
    const EMAIL_TO = 'email_to';

    /**
     * Nazwa nadawcy.
     */
    const EMAIL_NAME = 'email_name';

    /**
     * Odbiorcy kopii wiadomości.
     */
    const EMAIL_CC = 'email_cc';

    /**
     * Ukryci odbiorcy kopii wiadomości.
     */
    const EMAIL_BCC = 'email_bcc';

    /**
     * Port serwera.
     */
    const EMAIL_PORT = 'email_port';

    /**
     * Szyfrowanie połączenia.
     */
    const EMAIL_SSL = 'email_ssl';

    /**
     * wysyłka z użyciem biblioteki OpalBroker.
     */
    const EMAIL_USE_OPAL = 'email_use_opal';

    /**
     * ścieżka do OpalBroker.
     */
    const EMAIL_OPAL_PATH = 'email_opal_path';

    /**
     * MTA dla opalBroker.
     */
    const EMAIL_OPAL_MTA = 'email_opal_mta';

    /**
     * APP ID dla opalBroker.
     */
    const EMAIL_OPAL_APP_ID = 'email_opal_app_id';
    /**
     * Szerokość obrazka eventu.
     */
    const EVENT_IMAGE_WIDTH = 'event_image_width';
    /**
     * Wysokość obrazka eventu.
     */
    const EVENT_IMAGE_HEIGHT = 'event_image_height';
    /**
     * Wysokość miniatury eventu.
     */
    const EVENT_IMAGE_HEIGHT_THUMB = 'event_image_height_thumb';
    /**
     * Szerokość miniatury eventu.
     */
    const EVENT_IMAGE_WIDTH_THUMB = 'event_image_width_thumb';
    /**
     * Ilość branż
     */
    const BRAND_MAX = 'brand_max';

    const COMPANY_IMAGE_THUMB_WIDTH = 'company_image_thumb_width';

    const COMPANY_IMAGE_THUMB_HEIGHT = 'company_image_thumb_height';

    const EXHIB_STAND_LOGO_WIDTH = 'exhib_stand_logo_width';

    const EXHIB_STAND_LOGO_HEIGHT = 'exhib_stand_logo_height';

    /**
     * Logo na hali.
     */
    const EXHIB_STAND_LOGO_ON_HALL_WIDTH = 'exhib_stand_logo_on_hall_width';

    const EXHIB_STAND_LOGO_ON_HALL_HEIGHT = 'exhib_stand_logo_on_hall_height';

    /**
     * Stoisko standardowe.
     */
    const EXHIB_STAND_MAIN_SIGNBOARD_1_WIDTH = 'exhib_stand_main_signboard_1_width'; // szyld na scianie - szerokość
    const EXHIB_STAND_MAIN_SIGNBOARD_1_HEIGHT = 'exhib_stand_main_signboard_1_height'; // szyld na scianie - wysokość
    const EXHIB_STAND_MAIN_SIGNBOARD_1_PERSPECTIVE_POINTS = 'exhib_stand_main_signboard_1_perspective_points'; // szyld na scianie - perspective_points

    const EXHIB_STAND_DESC_SIGNBOARD_1_WIDTH = 'exhib_stand_desc_signboard_1_width'; // szyld na biurku - szerokość
    const EXHIB_STAND_DESC_SIGNBOARD_1_HEIGHT = 'exhib_stand_desc_signboard_1_height'; // szyld na biurku - wysokość
    const EXHIB_STAND_DESC_SIGNBOARD_1_PERSPECTIVE_POINTS = 'exhib_stand_desc_signboard_1_perspective_points'; // szyld na biurku - perspective_points

    const EXHIB_STAND_TV_MOVIE_1_WIDTH = 'exhib_stand_tv_movie_1_width'; // szyld na tv - szerokość
    const EXHIB_STAND_TV_MOVIE_1_HEIGHT = 'exhib_stand_tv_movie_1_height'; // szyld na tv - wysokość
    const EXHIB_STAND_TV_MOVIE_1_PERSPECTIVE_POINTS = 'exhib_stand_tv_movie_1_perspective_points'; // szyld na tv - perspective_points

    /**
     * Stoisko regionalne.
     */
    const EXHIB_STAND_MAIN_SIGNBOARD_2_WIDTH = 'exhib_stand_main_signboard_2_width'; // szyld na scianie - szerokość
    const EXHIB_STAND_MAIN_SIGNBOARD_2_HEIGHT = 'exhib_stand_main_signboard_2_height'; // szyld na scianie - wysokość
    const EXHIB_STAND_MAIN_SIGNBOARD_2_PERSPECTIVE_POINTS = 'exhib_stand_main_signboard_2_perspective_points'; // szyld na scianie - perspective_points

    const EXHIB_STAND_DESC_SIGNBOARD_2_WIDTH = 'exhib_stand_desc_signboard_2_width'; // szyld na biurku - szerokość
    const EXHIB_STAND_DESC_SIGNBOARD_2_HEIGHT = 'exhib_stand_desc_signboard_2_height'; // szyld na biurku - wysokość
    const EXHIB_STAND_DESC_SIGNBOARD_2_PERSPECTIVE_POINTS = 'exhib_stand_desc_signboard_2_perspective_points'; // szyld na biurku - perspective_points

    const EXHIB_STAND_TV_MOVIE_2_WIDTH = 'exhib_stand_tv_movie_2_width'; // szyld na tv - szerokość
    const EXHIB_STAND_TV_MOVIE_2_HEIGHT = 'exhib_stand_tv_movie_2_height'; // szyld na tv - wysokość
    const EXHIB_STAND_TV_MOVIE_2_PERSPECTIVE_POINTS = 'exhib_stand_tv_movie_2_perspective_points'; // szyld na tv - perspective_points

    /**
     * Stoisko sponsora głównego.
     */
    const EXHIB_STAND_MAIN_SIGNBOARD_3_WIDTH = 'exhib_stand_main_signboard_3_width'; // szyld na scianie - szerokość
    const EXHIB_STAND_MAIN_SIGNBOARD_3_HEIGHT = 'exhib_stand_main_signboard_3_height'; // szyld na scianie - wysokość
    const EXHIB_STAND_MAIN_SIGNBOARD_3_PERSPECTIVE_POINTS = 'exhib_stand_main_signboard_3_perspective_points'; // szyld na scianie - perspective_points

    const EXHIB_STAND_DESC_SIGNBOARD_3_WIDTH = 'exhib_stand_desc_signboard_3_width'; // szyld na biurku - szerokość
    const EXHIB_STAND_DESC_SIGNBOARD_3_HEIGHT = 'exhib_stand_desc_signboard_3_height'; // szyld na biurku - wysokość
    const EXHIB_STAND_DESC_SIGNBOARD_3_PERSPECTIVE_POINTS = 'exhib_stand_desc_signboard_3_perspective_points'; // szyld na biurku - perspective_points

    const EXHIB_STAND_TV_MOVIE_3_WIDTH = 'exhib_stand_tv_movie_3_width'; // szyld na tv - szerokość
    const EXHIB_STAND_TV_MOVIE_3_HEIGHT = 'exhib_stand_tv_movie_3_height'; // szyld na tv - wysokość
    const EXHIB_STAND_TV_MOVIE_3_PERSPECTIVE_POINTS = 'exhib_stand_tv_movie_3_perspective_points'; // szyld na tv - perspective_points

//    /**
//     * szyld główny stoiska standard
//     */
//    const EXHIB_STAND_MAIN_SIGNBOARD_WIDTH = 'exhib_stand_main_signboard_width';
//
//    const EXHIB_STAND_MAIN_SIGNBOARD_HEIGHT = 'exhib_stand_main_signboard_height';
//
//    const EXHIB_STAND_MAIN_SIGNBOARD_PERSPECTIVE_POINTS = 'exhib_stand_main_signboard_perspective_points';
//    /**
//     * szyld na biurku - stoisko standard
//     */
//    const EXHIB_STAND_DESC_SIGNBOARD_WIDTH = 'exhib_stand_desc_signboard_width';
//
//    const EXHIB_STAND_DESC_SIGNBOARD_HEIGHT = 'exhib_stand_desc_signboard_height';
//
//    const EXHIB_STAND_DESC_SIGNBOARD_PERSPECTIVE_POINTS = 'exhib_stand_desc_signboard_perspective_points';
//
//    /**
//     * szyld główny stoiska vip
//     */
//    const EXHIB_STAND_MAIN_SIGNBOARD_VIP_WIDTH = 'exhib_stand_main_signboard_vip_width';
//
//    const EXHIB_STAND_MAIN_SIGNBOARD_VIP_HEIGHT = 'exhib_stand_main_signboard_vip_height';
//
//    const EXHIB_STAND_MAIN_SIGNBOARD_VIP_PERSPECTIVE_POINTS = 'exhib_stand_main_signboard_vip_perspective_points';
//    /**
//     * szyld na biurku - stoisko vip
//     */
//    const EXHIB_STAND_DESC_SIGNBOARD_VIP_WIDTH = 'exhib_stand_desc_signboard_vip_width';
//
//    const EXHIB_STAND_DESC_SIGNBOARD_VIP_HEIGHT = 'exhib_stand_desc_signboard_vip_height';
//
//    const EXHIB_STAND_DESC_SIGNBOARD_VIP_PERSPECTIVE_POINTS = 'exhib_stand_desc_signboard_vip_perspective_points';
//
//    /**
//     * billboard na scianie - stoisko
//     */
//    const EXHIB_STAND_WALL_BILLBOARD_WIDTH = 'exhib_stand_wall_billboard_width';
//
//    const EXHIB_STAND_WALL_BILLBOARD_HEIGHT = 'exhib_stand_wall_billboard_height';
//
//    const EXHIB_STAND_WALL_BILLBOARD_PERSPECTIVE_POINTS = 'exhib_stand_wall_billboard_perspective_points';
//
//    /**
//     * film w TV - stoisko
//     */
//    const EXHIB_STAND_TV_MOVIE_WIDTH = 'exhib_stand_tv_movie_width';
//
//    const EXHIB_STAND_TV_MOVIE_HEIGHT = 'exhib_stand_tv_movie_height';
//
//    const EXHIB_STAND_TV_MOVIE_VIP_WIDTH = 'exhib_stand_tv_movie_vip_width';
//
//    const EXHIB_STAND_TV_MOVIE_VIP_HEIGHT = 'exhib_stand_tv_movie_vip_height';
//    /**
//     * wymiary własnego widoku stoiska
//     */
//    const EXHIB_STAND_OWN_VIEW_IMAGE_WIDTH = 'exhib_stand_own_view_image_width';
//
//    const EXHIB_STAND_OWN_VIEW_IMAGE_HEIGHT = 'exhib_stand_own_view_image_height';

    /**
     * Logo organizatora targów - rozmiary.
     */
    const EXHIB_EVENT_ORGANIZER_LOGO_WIDTH = 'exhib_event_organizer_logo_width';

    const EXHIB_EVENT_ORGANIZER_LOGO_HEIGHT = 'exhib_event_organizer_logo_height';

    /**
     * Targi - Hala z zewnątrz - bilbord.
     */
    const EXHIB_EVENT_BILLBOARD_OUTSIDE_HALL_WIDTH = 'exhib_event_billboard_outside_hall_width';

    const EXHIB_EVENT_BILLBOARD_OUTSIDE_HALL_HEIGHT = 'exhib_event_billboard_outside_hall_height';

    const EXHIB_EVENT_BILLBOARD_OUTSIDE_HALL_PERSPECTIVE_POINTS = 'exhib_event_billboard_outside_hall_perspective_points';

    /**
     * Targi - Recepcja - rozmiary logo na flashu.
     */
    const EXHIB_EVENT_RECEPTION_FLASH_LOGO_WIDTH = 'exhib_event_reception_flash_logo_width';

    const EXHIB_EVENT_RECEPTION_FLASH_LOGO_HEIGHT = 'exhib_event_reception_flash_logo_height';

    /**
     * Targi - Recepcja - rozmiary bannera na flashu.
     */
    const EXHIB_EVENT_RECEPTION_FLASH_BANNER_WIDTH = 'exhib_event_reception_flash_banner_width';

    const EXHIB_EVENT_RECEPTION_FLASH_BANNER_HEIGHT = 'exhib_event_reception_flash_banner_height';
    /**
     * Targi - Recepcja - rozmiary TV we flashu.
     */
    const EXHIB_EVENT_RECEPTION_FLASH_TV_1_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_1_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_1_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_1_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_1_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_1_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_2_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_2_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_2_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_2_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_2_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_2_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_3_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_3_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_3_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_3_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_3_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_3_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_4_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_4_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_4_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_4_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_4_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_4_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_5_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_5_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_5_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_5_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_5_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_5_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_6_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_6_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_6_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_6_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_6_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_6_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_7_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_7_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_7_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_7_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_7_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_7_movie_perspective_points';

    const EXHIB_EVENT_RECEPTION_FLASH_TV_8_MOVIE_WIDTH = 'exhib_event_reception_flash_tv_8_movie_width';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_8_MOVIE_HEIGHT = 'exhib_event_reception_flash_tv_8_movie_height';
    const EXHIB_EVENT_RECEPTION_FLASH_TV_8_MOVIE_PERSPECTIVE_POINTS = 'exhib_event_reception_flash_tv_8_movie_perspective_points';
    /**
     * Szerokość i wysokość miniatury dla plików graficznych stoiska.
     */
    const EXHIB_STAND_FILE_THUMB_WIDTH = 'exhib_stand_file_thumb_width';

    const EXHIB_STAND_FILE_THUMB_HEIGHT = 'exhib_stand_file_thumb_height';

    /**
     * Szerokość miniatury news.
     */
    const NEWS_IMAGE_THUMB_WIDTH = 'news_image_thumb_width';

    /**
     * Wysokość miniatury news.
     */
    const NEWS_IMAGE_THUMB_HEIGHT = 'news_image_thumb_height';
    /**
     * Szerokosc obrazka backgrounda na evencie.
     */
    const EVENT_BACKGROUND_IMAGE_WIDTH = 'background_image_width';
    /**
     * Wysokosc obrazka bacgrounda na evencie.
     */
    const EVENT_BACKGROUND_IMAGE_HEIGHT = 'background_image_height';
    /**
     * Szerokosc miniatury backgrounda na evencie.
     */
    const EVENT_BACKGROUND_IMAGE_THUMB_WIDTH = 'background_image_thumb_width';
    /**
     * Wysokosc miniatury obrazka miniatury na evencie.
     */
    const EVENT_BACKGROUND_IMAGE_THUMB_HEIGHT = 'background_image_thumb_height';
    /**
     * Szerokosc loga eventu.
     */
    const EVENT_LOGO_IMAGE_WIDTH = 'logo_image_width';
    /**
     * Wysokosc loga eventu.
     */
    const EVENT_LOGO_IMAGE_HEIGHT = 'logo_image_height';

    /**
     * Szerokosc miniatury loga eventu.
     */
    const EVENT_LOGO_IMAGE_THUMB_WIDTH = 'logo_image_thumb_width';
    /**
     * Wysokosc miniatury loga eventu.
     */
    const EVENT_LOGO_IMAGE_THUMB_HEIGHT = 'logo_image_thumb_height';

    /**
     * ID Aktywnego Eventu.
     */
    const HOME_PAGE_EVENT_ID = 'home_page_event_id';

    /**
     * Adres url chata Rhino.
     */
    const RHINO_URL = 'rhino_url';

    /**
     * Id grupy supportu chat.
     */
    const CHAT_SUPPORT_ID = 'chat_support_id';

    /**
     * Opoznienie auto pokazywania chata.
     */
    const CHAT_AUTOSHOW_DELAY = 'chat_autoshow_delay';

    const ADDITIONAL_CONTACT_FIELD_ON = 'additional_contact_field_on'; //dodatkowe pole w formularzu stoiska

    const SKIP_RECEPTION = 'skip_reception'; //omijanie recepcji przy wejściu

    const SHOW_GAMIFICATION = 'show_gamification'; //pokazywanie podstron grywalizacji

    const SHOW_WEBINARS = 'show_webinars'; //pokazywanie podstrony webinariów w recepcji

    const SHOW_PRICES = 'show_prices'; //wyświetlanie kolumny z ceną na listach produktów

    const GAMIFICATION_DELAY = 'gamification_delay';    //minimalny czas pomiędzy kliknięciami przy jakim beda naliczane punkty

    const EMAIL_REDIRECT_FORM = 'email_redirect_form';    //minimalny czas pomiędzy kliknięciami przy jakim beda naliczane punkty

    const USE_SSL = 'use_ssl';

    public static function getClearVariables()
    {
        return [
            self::EMAIL_SEND_ON => false,
            self::EMAIL_SMTP_ON => false,
            self::EMAIL_CRON_ON => false,
            self::EMAIL_TRANSPORTER => 'localhost',
            self::EMAIL_USER => '',
            self::EMAIL_PASSWORD => '',
            self::EMAIL_FROM => '',
            self::EMAIL_TO => '',
            self::EMAIL_NAME => DOMAIN,
            self::EMAIL_CC => '',
            self::EMAIL_BCC => '',
            self::EMAIL_PORT => '25',
            self::EMAIL_SSL => '',
            self::EMAIL_USE_OPAL => false,
            self::EMAIL_OPAL_PATH => '',
            self::EMAIL_OPAL_APP_ID => '',
            self::EVENT_IMAGE_HEIGHT => '',
            self::EVENT_IMAGE_WIDTH => '',
            self::EVENT_IMAGE_HEIGHT_THUMB => '',
            self::EVENT_IMAGE_WIDTH_THUMB => '',
            self::BRAND_MAX => 3,
            self::COMPANY_IMAGE_THUMB_HEIGHT => '',
            self::COMPANY_IMAGE_THUMB_WIDTH => '',
            self::NEWS_IMAGE_THUMB_WIDTH => '',
            self::NEWS_IMAGE_THUMB_HEIGHT => '',
            self::EXHIB_STAND_LOGO_WIDTH => '',
            self::EXHIB_STAND_LOGO_HEIGHT => '',
            self::EXHIB_STAND_LOGO_ON_HALL_WIDTH => '',
            self::EXHIB_STAND_LOGO_ON_HALL_HEIGHT => '',

            self::EXHIB_STAND_MAIN_SIGNBOARD_1_WIDTH => '',
            self::EXHIB_STAND_MAIN_SIGNBOARD_1_HEIGHT => '',
            self::EXHIB_STAND_MAIN_SIGNBOARD_1_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_DESC_SIGNBOARD_1_WIDTH => '',
            self::EXHIB_STAND_DESC_SIGNBOARD_1_HEIGHT => '',
            self::EXHIB_STAND_DESC_SIGNBOARD_1_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_TV_MOVIE_1_WIDTH => '',
            self::EXHIB_STAND_TV_MOVIE_1_HEIGHT => '',
            self::EXHIB_STAND_TV_MOVIE_1_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_MAIN_SIGNBOARD_2_WIDTH => '',
            self::EXHIB_STAND_MAIN_SIGNBOARD_2_HEIGHT => '',
            self::EXHIB_STAND_MAIN_SIGNBOARD_2_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_DESC_SIGNBOARD_2_WIDTH => '',
            self::EXHIB_STAND_DESC_SIGNBOARD_2_HEIGHT => '',
            self::EXHIB_STAND_DESC_SIGNBOARD_2_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_TV_MOVIE_2_WIDTH => '',
            self::EXHIB_STAND_TV_MOVIE_2_HEIGHT => '',
            self::EXHIB_STAND_TV_MOVIE_2_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_MAIN_SIGNBOARD_3_WIDTH => '',
            self::EXHIB_STAND_MAIN_SIGNBOARD_3_HEIGHT => '',
            self::EXHIB_STAND_MAIN_SIGNBOARD_3_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_DESC_SIGNBOARD_3_WIDTH => '',
            self::EXHIB_STAND_DESC_SIGNBOARD_3_HEIGHT => '',
            self::EXHIB_STAND_DESC_SIGNBOARD_3_PERSPECTIVE_POINTS => '',

            self::EXHIB_STAND_TV_MOVIE_3_WIDTH => '',
            self::EXHIB_STAND_TV_MOVIE_3_HEIGHT => '',
            self::EXHIB_STAND_TV_MOVIE_3_PERSPECTIVE_POINTS => '',

            self::EXHIB_EVENT_ORGANIZER_LOGO_WIDTH => '',
            self::EXHIB_EVENT_ORGANIZER_LOGO_HEIGHT => '',
            self::EXHIB_EVENT_BILLBOARD_OUTSIDE_HALL_WIDTH => '',
            self::EXHIB_EVENT_BILLBOARD_OUTSIDE_HALL_HEIGHT => '',
            self::EXHIB_EVENT_BILLBOARD_OUTSIDE_HALL_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_LOGO_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_LOGO_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_BANNER_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_BANNER_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_1_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_1_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_1_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_2_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_2_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_2_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_3_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_3_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_3_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_4_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_4_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_4_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_5_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_5_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_5_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_6_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_6_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_6_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_7_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_7_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_7_MOVIE_PERSPECTIVE_POINTS => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_8_MOVIE_WIDTH => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_8_MOVIE_HEIGHT => '',
            self::EXHIB_EVENT_RECEPTION_FLASH_TV_8_MOVIE_PERSPECTIVE_POINTS => '',
            self::EVENT_BACKGROUND_IMAGE_WIDTH => '',
            self::EVENT_BACKGROUND_IMAGE_HEIGHT => '',
            self::EVENT_BACKGROUND_IMAGE_THUMB_WIDTH => '',
            self::EVENT_BACKGROUND_IMAGE_THUMB_HEIGHT => '',
            self::EVENT_LOGO_IMAGE_WIDTH => '',
            self::EVENT_LOGO_IMAGE_HEIGHT => '',
            self::EVENT_LOGO_IMAGE_THUMB_WIDTH => '',
            self::EVENT_LOGO_IMAGE_THUMB_HEIGHT => '',
            self::HOME_PAGE_EVENT_ID => null,
            self::ADDITIONAL_CONTACT_FIELD_ON => '',
            self::SKIP_RECEPTION => '',
            self::SHOW_GAMIFICATION => '',
            self::SHOW_WEBINARS => '',
            self::SHOW_PRICES => '',
            self::GAMIFICATION_DELAY => '',
            self::EMAIL_REDIRECT_FORM => '',
            self::USE_SSL => false,
        ];
    }

    public static function getDefaultVariables()
    {
        $variables = Doctrine_Query::create()
            ->from('Variable v')
            ->where('v.id_base_user IS NULL')
            ->execute()
            ->toKeyValueArray('name', 'value')
        ;

        return array_merge(self::getClearVariables(), $variables);
    }

    public static function getBaseUserVariables($id_base_user)
    {
        $variables = Doctrine_Query::create()
            ->from('Variable v')
            ->where('v.id_base_user = ?', $id_base_user)
            ->execute()
            ->toKeyValueArray('name', 'value')
        ;

        return array_merge(self::getDefaultVariables(), $variables);
    }

    public static function setVariable($name, $value)
    {
        Doctrine_Query::create()
            ->update('Variable v')
            ->where('v.name = ?', $name)
            ->set('v.value', '?', $value)
            ->execute()
        ;
    }
}
