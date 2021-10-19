<?php

/**
 * EventTranslation.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class EventTranslation extends Table_EventTranslation
{
    /**
     * @param $id
     * @param $id_language
     *
     * @return EventTranslation
     */
    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine_Query::create()
            ->from(__CLASS__)
            ->where('id_event = ? AND id_language = ?', [$id, $id_language])
            ->limit(1)
            ->execute()
            ->getFirst()
        ;
    }

    public function setMapSponsors($value)
    {
        return $this->map_sponsors = json_encode($value);
    }

    public function getMapSponsors()
    {
        return $names = Zend_Json::decode($this->map_sponsors);
    }

    public function getMapSponsorsRaw()
    {
        return $this->map_sponsors;
    }

    public function setMapSponsorsRaw($data)
    {
        $this->map_sponsors = $data;
    }
}