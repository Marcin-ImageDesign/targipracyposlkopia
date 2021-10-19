<?php

/**
 * ExhibStand.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ExhibStand extends Table_ExhibStand implements Engine_Doctrine_Record_IdentifiableInterface
{
    const DIRECTORY = '_db/exhib_stand';

    const INQUIRY_CHANNEL = 'stand';
    const INQUIRY_CHANNEL_WWW_SITE = 'stand-www-site';

    /**
     * Stoisko standardowe.
     */
    const TYPE_STANDARD = 1;

    /**
     * Stoisko testowe.
     */
    const TYPE_TEST = 2;

    /**
     * Poziomy (typy) stoisk.
     */
    const STAND_LEVEL_STANDARD = 1;
    const STAND_LEVEL_REGIONAL = 2;
    const STAND_LEVEL_MAIN = 3;

    public $movie_ext = ['flv'];

    public static $standViewFiles = ['main_signboard', 'desc_signboard', 'wall_billboard', 'tv_movie'];

    private $_exhibitorList;

    private $_data_banner;

    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine::getTable('ExhibStand')->find($id);
    }

    public static function findAll()
    {
        return Doctrine::getTable('ExhibStand')->findAll();
    }

    // public function getId() {
    //     return $this->id_exhib_stand;
    // }

    // public function getName() {
    //     if(
    //     $this->id_exhib_stand_type == ExhibStandType::CONFERENCE_HALL &&
    //     $this->relatedExists('ExhibConferenceHall')
    //     ){
    //     return $this->ExhibConferenceHall->getName();
    //     }

    //     return $this->name;
    // }

//    public function getGoogleAnalytics()
//    {
//        return $this->google_analytics;
//    }

    public function isFbAddress()
    {
        return !empty($this->fb_address) ? true : false;
    }

    public function isSkypeName()
    {
        return !empty($this->skype_name) ? true : false;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function getFbAddress($prefix = 'http://')
    {
        if (!$this->isFbAddress()) {
            $prefix = '';
        }

        return $prefix . $this->fb_address;
    }

    public function getSkypeNameAdress()
    {
        if (!$this->isSkypeName()) {
            $prefix = '';
        }

        return 'skype:live:' . $this->skype_name . '?call';
    }

    public function setWwwAdress($value)
    {
        $value = str_replace('http://', '', $value);
        $id_language = Engine_I18n::getLangId();

        return $this->_setField('www_adress', $value, $id_language);
    }

//    public function getWwwAdress(){
//        return $this->www_adress;
//    }

//    public function setName($value) {
//        return $this->name = $value;
//    }

    public function getDataBanner()
    {
        if (null === $this->_data_banner) {
            $this->_data_banner = (array) @json_decode($this->data_banner, true);
        }

        return $this->_data_banner;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setDataBanner($key, $value)
    {
        $this->_data_banner[$key] = $value;
        $this->data_banner = json_encode($this->_data_banner);

        return $this;
    }

    public function setDataBanners($value)
    {
        $this->data_banner = json_encode($value);

        return $this;
    }

    public function isActiveChat()
    {
        return (bool) $this->is_active_chat;
    }

    public function isVip()
    {
//        return $this->ExhibStandLevel->getId() == ExhibStandLevel::VIP;
        return StandLevel::LEVEL_MAIN === $this->id_stand_level;
    }

    public function isNew()
    {
        return null === $this->getId();
    }

    public function fileTvIsMovie()
    {
        if (!empty($this->tv_movie_ext)) {
            return in_array($this->tv_movie_ext, $this->movie_ext, true);
        }

        return false;
    }

    public function getCountProducts()
    {
        if (null === $this->count_products) {
            $this->count_products = Doctrine_Query::create()
                ->select('count(*) as count')
                ->from('StandProduct sp')
                ->where('sp.id_exhib_stand = ?', $this->getId())
                ->addWhere('sp.is_active = 1')
                ->limit(1)
                ->execute()
                ->getFirst()
                ->count;

            $this->save();
        }

        return $this->count_products;
    }

    public function getCountBargains()
    {
        if (null === $this->count_bargains) {
            $this->count_bargains = Doctrine_Query::create()
                ->select('count(*) as count')
                ->from('StandProduct sp')
                ->where('sp.id_exhib_stand = ?', $this->getId())
                ->addWhere('sp.is_active = 1')
                ->addWhere('sp.is_promotion = 1')
                ->limit(1)
                ->execute()
                ->getFirst()
                ->count;

            $this->save();
        }

        return $this->count_bargains;
    }

    public function getCountVideos()
    {
        if (null === $this->count_videos) {
            $this->count_videos = Doctrine_Query::create()
                ->select('count(*) as count')
                ->from('StandVideo sv')
                ->where('sv.id_exhib_stand = ?', $this->getId())
                ->addWhere('sv.is_active = 1')
                ->limit(1)
                ->execute()
                ->getFirst()
                ->count;

            $this->save();
        }

        return $this->count_videos;
    }

    public function getCountFiles()
    {
        if (null === $this->count_files) {
            $this->count_files = Doctrine_Query::create()
                ->select('count(*) as count')
                ->from('ExhibStandFile esf')
                ->where('esf.id_exhib_stand = ?', $this->getId())
                ->addWhere('esf.is_visible = 1')
                ->limit(1)
                ->execute()
                ->getFirst()
                ->count;

            $this->save();
        }

        return $this->count_files;
    }

    public function getStandVideos()
    {
        $map_videos_array = [];

        if (null === $this->map_videos) {
            $map_videos_query = Doctrine_Query::create()
                ->select('sv.video_link')
                ->from('StandVideo sv')
                ->where('sv.is_active = 1')
                ->addWhere('sv.show_on_stand = 1')
                ->addWhere('sv.id_exhib_stand = ?', (int) $this->getId())
                ->execute([], Doctrine::HYDRATE_ARRAY)
            ;

            foreach ($map_videos_query as $video) {
                $map_videos_array[] = $video['video_link'];
            }

            $this->map_videos = json_encode($map_videos_array);
            $this->save();
        }
        $video_keys = json_decode($this->map_videos, true);
        if (null !== $video_keys && !empty($video_keys)) {
            $first = array_rand($video_keys, 1);

            return $video_keys[$first];
        }

        return false;
    }

    /**
     * @param string   $hash
     * @param BaseUser $baseUser
     *
     * @return ExhibStand
     */
    public static function findOneByHashAndBaseUser($hash, $baseUser)
    {
        return Doctrine::getTable('ExhibStand')->findOneByHashAndIdBaseUser($hash, $baseUser->getId());
    }

    public function getLogoOnHallFileName($type = '')
    {
        return $this->getStandViewFileName('logo_on_hall', $type);
    }

    public function getLogoFileName($type = '')
    {
        return $this->getStandViewFileName('logo', $type);
    }

    public function getMainSignboardFileName($type = '')
    {
        return $this->getStandViewFileName('main_signboard', $type);
    }

    public function getDescSignboardFileName($type = '')
    {
        return $this->getStandViewFileName('desc_signboard', $type);
    }

    public function getWallBillboardFileName($type = '')
    {
        return $this->getStandViewFileName('wall_Billboard', $type);
    }

    //Metody do obsługi plików stoiska
    public function setStandViewFileExt($name, $ext)
    {
        $this->{$name . '_ext'} = $ext;
    }

    public function getRelativeStandViewFile($name, $type = '', $createDir = true)
    {
        return $this->getRelativePath($createDir) . DS . $this->getStandViewFileName($name, $type);
    }

    public function deleteStandViewFile($name)
    {
        if ($this->standViewFileExists($name)) {
            unlink($this->getRelativeStandViewFile($name, '', false));
        }
        $this->{$name . '_ext'} = '';
    }

    public function getRelativePath($createDir = true)
    {
        $relativePath = $this->BaseUser->getPublicRelativePath($createDir);
        if ($createDir) {
            $utils = Engine_Utils::getInstance();
            $relativePath = $utils->createDirWithPath($relativePath, self::DIRECTORY, '/');
        } else {
            $relativePath = $relativePath . DS . self::DIRECTORY;
        }

        return $relativePath;
    }

    public function getRelativeLogo($createDir = true)
    {
        return $this->getRelativeStandViewFile('logo', '', $createDir);
    }

    public function getRelativeMainSignboard($createDir = true)
    {
        return $this->getRelativeStandViewFile('main_signboard', '', $createDir);
    }

    public function getRelativeDescSignboard($createDir = true)
    {
        return $this->getRelativeStandViewFile('desc_signboard', '', $createDir);
    }

    public function getRelativeWallBillboard($createDir = true)
    {
        return $this->getRelativeStandViewFile('wall_billboard', '', $createDir);
    }

    public function getRelativeTvMovie($createDir = true)
    {
        return $this->getRelativeStandViewFile('tv_movie', '', $createDir);
    }

    public function getBrowserPath()
    {
        return $this->BaseUser->getPublicBrowserPath() . '/' . self::DIRECTORY;
    }

    public function getBrowserLogoOnHall()
    {
        return $this->getBrowserStandViewFile('logo_on_hall');
    }

    public function getBrowserLogo()
    {
        return $this->getBrowserStandViewFile('logo');
    }

    public function getBrowserMainSignboard($type = '')
    {
        return $this->getBrowserStandViewFile('main_signboard', $type);
    }

    public function getBrowserDescSignboard($type = '')
    {
        return $this->getBrowserStandViewFile('desc_signboard', $type);
    }

    public function getBrowserWallBillboard()
    {
        return $this->getBrowserStandViewFile('wall_billboard');
    }

    public function getBrowserTvMovie($type = '')
    {
        return $this->getBrowserStandViewFile('tv_movie', $type);
    }

    public function isImageLogoExists()
    {
        return !empty($this->id_image_logo) ? true : false;
    }

    public function isImageFbExists()
    {
        return !empty($this->id_image_fb) ? true : false;
    }

    public function isImageHallExists()
    {
        return !empty($this->id_image_hall) ? true : false;
    }

    public function getChatGroupId()
    {
        return $this->live_chat_group_id;
    }

    public function deleteLogo()
    {
        $this->deleteStandViewFile('logo');
    }

    public function deleteMainSignboard()
    {
        $this->deleteStandViewFile('main_signboard');
    }

    public function deleteDescSignboard()
    {
        $this->deleteStandViewFile('desc_signboard');
    }

    public function deleteWallBillboard()
    {
        $this->deleteStandViewFile('wall_billboard');
    }

    public function deleteTvMovie()
    {
        $this->deleteStandViewFile('tv_movie');
    }

    public function logoOnHallExists()
    {
        return $this->standViewFileExists('logo_on_hall');
    }

    public function logoExists()
    {
        return $this->standViewFileExists('logo');
    }

    public function mainSignboardExists()
    {
        return $this->standViewFileExists('main_signboard');
    }

    public function descSignboardExists()
    {
        return $this->standViewFileExists('desc_signboard');
    }

    public function wallBillboardExists()
    {
        return $this->standViewFileExists('wall_billboard');
    }

    public function tvMovieExists()
    {
        return $this->standViewFileExists('tv_movie');
    }

    public function getExhibitorsList()
    {
        if (null === $this->_exhibitorList) {
            $this->_exhibitorList = Doctrine_Query::create()
                ->from('User u INDEXBY u.hash')
                ->innerJoin('u.ExhibParticipation ep INDEXBY ep.hash')
                ->innerJoin('ep.ExhibStandParticipation esp INDEXBY esp.hash')
                ->where(
                    'ep.is_active = 1 AND ep.id_exhib_participation_type = ?',
                    [ExhibParticipationType::TYPE_EXHIBITOR]
                )
                ->addWhere(
                    'esp.is_active = 1 AND esp.id_exhib_stand = ?',
                    [$this->getId()]
                )
                ->execute()
            ;
        }

        return $this->_exhibitorList;
    }

    public function hasExhibitor(User $user)
    {
        $exhibitorsList = $this->getExhibitorsList();

        return isset($exhibitorsList[$user->getHash()]);
    }

    public function hasActiveChat()
    {
        $return = false;

        if ($this->is_active_chat && $this->ChatRoom->is_active) {
            if (strtotime($this->ChatRoom->modificated_at) + 30 < time()) {
                $this->ChatRoom->clearRoom();
            } elseif ($this->ChatRoom->user_count < 2) {
                $return = true;
            }
        }

        return $return;
    }

    // metoda do wyrzucenia po przerobieniu linków na liście stoisk z hashy StandParticipation na Stand
    public static function findOneBySpHash($hash)
    {
        $query = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->where('sp.hash = ?', [$hash])
        ;

        return $query->execute()->getFirst();
    }

    /**
     * @param $hash
     * @param null $language
     *
     * @return ExhibStand
     */
    public static function findOneByHash($hash, $language = null)
    {
        $query = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->leftJoin('s.ExhibStandFile sf WITH sf.is_visible = 1 ')
            ->where('s.hash = ?', [$hash])
        ;

        // wyrzuciłem sp.is_active z where sp.is_active = 1 AND
        return $query->execute()->getFirst();
    }

    public static function findOneByUri($uri, $baseUser, $id_event, $hall_uri, $language = null)
    {
        $query = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->leftJoin('s.ExhibStandFile sf WITH sf.is_visible = 1 ')
            ->leftJoin('s.EventStandNumber esn')
            ->leftJoin('esn.EventHallMap ehm')
            ->where('t.uri = ?', [$uri])
            ->addWhere('s.id_event = ?', $id_event)
            ->addWhere('ehm.uri = ?', $hall_uri)
            ->limit(1)
        ;

        // wyrzuciłem sp.is_active z where sp.is_active = 1 AND
        return $query->fetchOne();
    }

    /**
     * @param int $id_base_user
     * @param int $id_event
     *
     * @return ExhibStand
     */
    public static function findStandsByEvent($id_base_user, $id_event)
    {
        $query = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->leftJoin('sp.ExhibParticipation p')
            ->where(
                ' p.id_exhib_participation_type IN (1,2)
                AND p.id_event = ? AND p.id_base_user = ? AND s.id_exhib_stand_type = ?',
                [$id_event, $id_base_user, ExhibStandType::STANDARD]
            )
        ;

        return $query->execute();
    }

    /**
     * @param int $id_base_user
     * @param int $id_event
     * @param int $id_participation_type
     *
     * @return ExhibParticipation
     */
    public static function findStands($id_base_user, $id_event)
    {
        $query = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->leftJoin('sp.ExhibParticipation p')
            ->where(' sp.is_active = 1 AND p.is_active = 1 AND s.is_active = 1 AND p.id_exhib_participation_type IN (1,2)
                AND p.id_event = ? AND p.id_base_user = ? AND s.id_exhib_stand_type = ?', [$id_event, $id_base_user, ExhibStandType::STANDARD])
        ;

        return $query->execute();
    }

    /**
     * @param int        $baseUser
     * @param int        $userAuth
     * @param null|mixed $id_event
     *
     * @return ExhibStands
     */
    public static function findStandsByAuthUser($baseUser, $userAuth, $id_event = null)
    {
        $standQuery = Doctrine_Query::create()
            ->from('ExhibStand s')
            ->leftJoin('s.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('s.ExhibStandParticipation sp')
            ->leftJoin('sp.ExhibParticipation p')
            ->where('s.id_exhib_stand_type = ? AND s.id_base_user = ?', [ExhibStandType::STANDARD, $baseUser->getId()])
            ->orderBy('t.name')
        ;
        $user_role_id = $userAuth->UserRole->getId();
        if (!empty($id_event)) {
            $standQuery->addWhere('p.id_event = ?', [$id_event]);
        }
        //jeżeli zalogowany wystawca
        if (!empty($user_role_id)) {
            if (UserRole::ROLE_EXHIBITOR == $user_role_id) {
                $standQuery->addWhere('p.id_exhib_participation_type = ? AND p.id_user = ? AND sp.is_active = 1 AND p.is_active = 1 ', [ExhibParticipationType::TYPE_EXHIBITOR, $userAuth->getId()]);
            }
        }

        //jesli zalogowany organizator wyswietlamy tylko jego wystawców
        if (UserRole::ROLE_ORGANIZER == $user_role_id) {
            $events_organizer = ExhibParticipation::findEventsOrganizer($userAuth->getId());
            if (!empty($events_organizer)) {
                $standQuery->addWhere(' p.is_active = 1 AND sp.is_active = 1 AND ( p.id_event IN (' . $events_organizer . ') OR ( p.id_user = ? )) ', [$userAuth->getId()]);
            } else {
                $standQuery->where(' 0 = 1 ');
            }
        }

        return $standQuery->execute();
    }

    /**
     * metoda sprawdzajaca dostep do stoiska.
     *
     * @return bool
     */
    public function hasAccess(User $user)
    {
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($this->hasExhibitor($user)) {
            return true;
        }

        return false;
    }

    /**
     * Klonuje stoisko.
     *
     * @param [type] $stand       stare stoisko
     * @param [type] $standNumber numer nowego stoiska
     * @param [type] $hallNumber  hala nowego stoiska
     *
     * @return [type] [description]
     */
    public function cloneStand($stand, $standNumber, $hallNumber)
    {
        $newStand = new ExhibStand();
        $newStand->BaseUser = $this->getSelectedBaseUser();
        $newStand->hash = $this->engineUtils->getHash();

        //tłumaczenia
        $newStand->setName($stand->getName());
        $newStand->setUri($stand->getUri());
        $newStand->setContactInfo($stand->getContactInfo());
        $newStand->setShortContact($stand->getShortContact());
        $newStand->setShortInfo($stand->getShortInfo());
        $newStand->setWwwAdress($stand->getWwwAdress());
        $newStand->setExhibitorInfo($stand->getExhibitorInfo());
        $newStand->setStandKeywords($stand->getStandKeywords());
        //ExhibStand
        $newStand->id_event = $stand->id_event;
        $newStand->id_stand_type = $stand->id_stand_type;
        $newStand->id_image_logo = $stand->id_image_logo;
        $newStand->id_image_fb = $stand->id_image_fb;
        $newStand->id_image_hall = $stand->id_image_hall;
        $newStand->id_image_banner_desk = $stand->id_image_banner_desk;
        $newStand->id_image_banner_top = $stand->id_image_banner_top;
        $newStand->id_image_banner_tv = $stand->id_image_banner_tv;
        $newStand->data_banner = $stand->data_banner;
        $newStand->is_active_chat = $stand->is_active_chat;
        $newStand->live_chat_group_id = $stand->live_chat_group_id;
        $newStand->id_exhib_stand_type = $stand->id_exhib_stand_type;
        //numer stoiska
        $newStand->id_event_stand_number = $standNumber;
        $newStand->id_stand_level = $stand->id_stand_level;
        $newStand->is_active = $stand->is_active;
        $newStand->id_exhib_stand_hostess = $stand->id_exhib_stand_hostess;
        $newStand->id_exhib_stand_view_image = $stand->id_exhib_stand_view_image;
        $newStand->main_signboard_ext = $stand->main_signboard_ext;
        $newStand->tv_movie_ext = $stand->tv_movie_ext;
        $newStand->fb_address = $stand->fb_address;
        //numer hali
        $newStand->hall_number = $hallNumber;
        $newStand->is_owner_view = $stand->is_owner_view;
        $newStand->wall_billboard_ext = $stand->wall_billboard_ext;
        $newStand->id_exhib_stand_participation = $stand->id_exhib_stand_participation;
        $newStand->id_address_province = $stand->id_address_province;
        $newStand->count_videos = $stand->count_videos;
        //nie klonujemy produktów, więc zerujemy countery
        $newStand->count_products = 0;
        $newStand->count_bargains = 0;
        $newStand->count_files = $stand->count_files;
        $newStand->map_videos = $stand->map_videos;
        $newStand->google_analytics = $stand->google_analytics;
        $newStand->css_class = $stand->css_class;

        //metatagi
        $newStand->is_metatag = $stand->is_metatag;
        $newStand->setMetatagTitle($stand->getMetatagTitle());
        $newStand->setMetatagDesc($stand->getMetatagDesc());
        $newStand->setMetatagKey($stand->getMetatagKey());

        return $newStand;
    }

    public function getPublishedFilesCount()
    {
        return Doctrine_Query::create()
            ->from('ExhibStandFile esf')
            ->where('esf.id_exhib_stand = ?', $this->id_exhib_stand)
            ->addWhere('esf.is_published = 1')
            ->addWhere('esf.is_visible = 1')
            ->execute()
            ->count()
        ;
    }

    public function getField($field, $id_language = null)
    {
        return $this->getTransaltion($id_language)->{$field};
    }

    public function setField($field, $value, $id_language = null)
    {
        $this->getTransaltion($id_language)->{$field} = $value;
    }

    public function getMetatagTitle()
    {
        return $this->getField('metatag_title');
    }

    public function setMetatagTitle($value)
    {
        $this->setField('metatag_title', $value);
    }

    public function getMetatagDesc()
    {
        return $this->getField('metatag_desc');
    }

    public function setMetatagDesc($value)
    {
        $this->setField('metatag_desc', $value);
    }

    public function getMetatagKey()
    {
        return $this->getField('metatag_key');
    }

    public function setMetatagKey($value)
    {
        $this->setField('metatag_key', $value);
    }

    private function getStandViewFileName($name, $type = '')
    {
        if (!empty($type)) {
            $type = $type . '_';
        }

        return $name . '_' . $type . $this->hash . '.' . $this->{$name . '_ext'};
    }

    private function getBrowserStandViewFile($name, $type = '')
    {
        if ($this->standViewFileExists($name, $type)) {
            return $this->getBrowserPath() . '/' . $this->getStandViewFileName($name, $type);
        }

        return false;
    }

    private function standViewFileExists($name, $type = '')
    {
        if (!empty($this->{$name . '_ext'}) && file_exists($this->getRelativeStandViewFile($name, $type, false))) {
            return true;
        }

        return false;
    }
}
