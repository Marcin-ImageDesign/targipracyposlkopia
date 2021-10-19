<?php

class Image extends Table_Image implements Engine_Doctrine_Record_IdentifiableInterface
{
    const SECRET_KEY = '98asdf;**&**&&fasf__--)))999%$saasddd...9';

    const RESIZE_ORYGINAL = 'o';
    const RESIZE_NO = 'n';
    const RESIZE_CROP = 'c';
    const RESIZE_TO_WIDTH = 'w';
    const RESIZE_TO_HEIGHT = 'h';
    const RESIZE_TO_BOTH = 'b';
    const RESIZE_TO_BOTH_IF_BIGGER = 'bi';
    const RESIZE_TO_BOTH_AND_FILL = 'm';
    const RESIZE_ADAPTIVE = 'a';

    const PARAM_PERSPECTIVE = 'p';
    const PARAM_CROP = 'c';

    /**
     * @param $id_image
     *
     * @return false|Image
     */
    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine::getTable(__CLASS__)->find($id);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id_image;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return null === $this->getId() ? true : false;
    }

    /**
     * Zwraca adres url do zdjęcia.
     *
     * @param int    $width
     * @param int    $height
     * @param string $resize
     * @param mixed  $params
     *
     * @return string
     */
    public function getUrl($params = [])
    {
        return Service_Image::getUrl($this->id_image, $this->getParams($params));
    }

    public function getOrginalUrl()
    {
        return Service_Image::getUrl($this->id_image, ['resize' => self::RESIZE_ORYGINAL]);
    }

    public function getParams(array $params = [])
    {
        $default = ['resize' => 'b', 'width' => 100, 'height' => 100];
        $img_params = [];
        if (!empty($this->params)) {
            $img_params = json_decode($this->params, true);
        }

        return array_merge($default, $img_params, $params);
    }

    /**
     * @param array $params
     *
     * @return Image
     */
    public function setParams($params = [])
    {
        $this->params = json_encode($params);

        return $this;
    }

    public static function getHashKey($params)
    {
        return mb_substr(md5(self::SECRET_KEY . implode('-', $params)), 0, 5);
    }

    public function preSave($event)
    {
        if (!$this->isNew()) {
            Service_Image::cleanCache($this);
        }
    }
}
