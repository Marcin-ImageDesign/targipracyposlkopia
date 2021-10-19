<?php

/**
 * EventHallMap.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class EventHallMap extends Table_EventHallMap implements Engine_Doctrine_Record_IdentifiableInterface
{
    private $_dataHallMap;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id_event_hall_map;
    }

    /**
     * @param $uri
     * @param $id_event
     *
     * @return EventHallMap
     */
    public static function findOneByUriAndIdEvent($uri, $id_event)
    {
        return Doctrine_Query::create()
            ->from(__CLASS__)
            ->where('uri = ? AND id_event = ?', [$uri, $id_event])
            ->limit(1)
            ->execute()
            ->getFirst()
        ;
    }

    public static function findTemplateByUri($uri)
    {
        return Doctrine_Query::create()
            ->from(__CLASS__)
            ->where('uri = ? AND is_template = 1', [$uri])
            ->limit(1)
            ->execute()
            ->getFirst()
        ;
    }

    public function setHallMap($value)
    {
        if (!isset($value['attribs'])) {
            $value = [
                'attribs' => [
                    'id' => 'mymap',
                    'name' => 'mymap',
                ],
                'items' => $value,
            ];
        }

        $this->hall_map = json_encode($value);

        return $this;
    }

    public function getHallMap()
    {
        if (null === $this->_dataHallMap) {
            $this->_dataHallMap = (array) @json_decode($this->hall_map, true);
        }

        return $this->_dataHallMap;
    }

    public function setZoomData($value)
    {
        if (0 === count(array_filter($value))) {
            $value = null;
        } else {
            $value = json_encode($value);
        }

        return $this->zoom_data = $value;
    }

    public function setZoomDataRaw($value)
    {
        return $this->zoom_data = $value;
    }

    public function getZoomDataRaw()
    {
        return $this->zoom_data;
    }

    public function getZoomData($param = null)
    {
        $data = json_decode($this->zoom_data, true);
        if (!is_null($param)) {
            $data = null !== $data[$param] ? $data[$param] : null;
        }

        return $data;
    }

    public function hasZoomData()
    {
        return null !== $this->zoom_data ? true : false;
    }

    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine::getTable(__CLASS__)->find($id);
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getImage()
    {
        return $this->id_image;
    }

    public function getEvent()
    {
        return $this->Event;
    }

    public static function findOneByHash($hash)
    {
        return Doctrine::getTable(__CLASS__)->findOneByHash($hash);
    }

    public function getHallMapRaw()
    {
        return $this->hall_map;
    }

    public function setHeight($value)
    {
        return $this->height = $value;
    }

    public function setWidth($value)
    {
        return $this->width = $value;
    }

    public function setImage($value)
    {
        return $this->id_image = $value;
    }

    public function setHallMapRaw($value)
    {
        return $this->hall_map = $value;
    }

    public function hasEvent()
    {
        return isset($this->Event) ? true : false;
    }

    public static function getHallMapsByEvent($id_event)
    {
        $id_language = Engine_I18n::getLangId();

        return Doctrine_Query::create()
            ->select('ehm.id_event_hall_map, ehm.uri, t.name as name, t.description as description, ehm.id_promo_photo, ehm.id_image')
            ->from('EventHallMap ehm INDEXBY ehm.id_event_hall_map')
            ->leftJoin('ehm.Translations t WITH t.id_language = ?', $id_language)
            ->where('id_event = ?', [$id_event])
            ->orderBy('IFNULL(ehm.order, ehm.id_event_hall_map)')
            ->execute([], Doctrine::HYDRATE_ARRAY)
        ;
    }
}