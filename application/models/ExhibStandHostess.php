<?php

/**
 * ExhibStandHostess.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ExhibStandHostess extends Table_ExhibStandHostess
{
    const DIRECTORY_HOSTESSES = '_db/exhib_stand_hostess/';

    public function getId()
    {
        return $this->id_exhib_stand_hostess;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        return $this->name = $value;
    }

    public function getIsAnimated()
    {
        return $this->is_animated;
    }

    public function setConfig($value)
    {
        return $this->data_map = json_encode($value);
    }

    public function getConfig()
    {
        if (empty($this->data_map)) {
            return [];
        }

        return json_decode($this->data_map, true);
    }

    public static function getPublicPathImageHostesses(BaseUser $BaseUser)
    {
        return $BaseUser->getPublicBrowserPath() . '/' . self::DIRECTORY_HOSTESSES;
    }

    public function getHostessThumbFileName()
    {
        return 'thumb_' . $this->id_exhib_stand_hostess . '.' . $this->file_ext;
    }

    public function getHostessFileName()
    {
        return $this->id_exhib_stand_hostess . '.' . $this->file_ext;
    }

    public function getBrowseHostess()
    {
        return $this->BaseUser->getPublicBrowserPath() . '/' . self::DIRECTORY_HOSTESSES . $this->getHostessFileName();
    }

    public function getBrowserThumbHostess()
    {
        return $this->BaseUser->getPublicBrowserPath() . '/' . self::DIRECTORY_HOSTESSES . $this->getHostessThumbFileName();
    }
}
