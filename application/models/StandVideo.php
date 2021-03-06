<?php

/**
 * StandVideo.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class StandVideo extends Table_StandVideo
{
    const YT_LINK_PREFIX = 'https://www.youtube.com/watch?v=';

    public function getId()
    {
        return $this->id_stand_video;
    }

    public function setName($value)
    {
        return $this->setField('name', $value);
    }

    public function getName()
    {
        return $this->getField('name');
    }

    public function setLead($value)
    {
        return $this->setField('lead', $value);
    }

    public function getLead()
    {
        return $this->getField('lead');
    }

    public function setVideoLink($value)
    {
        return $this->video_link = $this->parse_yturl($value);
    }

    public function getVideoLink()
    {
        return self::YT_LINK_PREFIX . $this->video_link;
    }

    public function getVideoKey()
    {
        return $this->video_link;
    }

    public function getVideoPhoto()
    {
        //$json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/videos/".$this->getVideoKey()."?v=2&alt=jsonc"));
        //http://img.youtube.com/vi/hVHiy_6UnX8/0.jpg
        //return $json;
        return 'https://img.youtube.com/vi/' . $this->getVideoKey() . '/0.jpg';
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setIsActive($value)
    {
        return $this->is_active = $value;
    }

    public function getIsActive()
    {
        return (bool) $this->is_active;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setShowOnStand($value)
    {
        return $this->show_on_stand = $value;
    }

    public function getShowOnStand()
    {
        return (bool) $this->show_on_stand;
    }

    /**
     * @return bool
     */
    public function translationExists()
    {
        $name = $this->getName();

        return empty($name) ? false : true;
    }

    /**
     * @param string $hash
     *
     * @return StandVideo
     */
    public function findOneByHash($hash)
    {
        return Doctrine::getTable(__CLASS__)->findOneByHash($hash);
    }

    public static function find($id, $id_language = null, $tableName = null)
    {
        $id_language = null === $id_language ? Engine_I18n::getLangId() : $id_language;

        return Doctrine_Query::create()
            ->from('StandVideo sv')
            ->leftJoin('sv.Translations t WITH t.id_language = ?', $id_language)
            ->where('sv.hash = ?', $id)
            ->limit(1)
            ->execute()
            ->getFirst()
        ;
    }

    public static function findOneByYtKey($key)
    {
        return Doctrine_Query::create()
            ->from('StandVideo sv')
            ->where('sv.video_link = ?', $key)
            ->execute()
            ->getFirst()
        ;
    }

    public function getField($field)
    {
        return $this->getTranslation()->{$field};
    }

    public function setField($field, $value)
    {
        $this->getTranslation()->{$field} = $value;
    }

    /**
     * @param null|int $id_language
     *
     * @return StandVideoTranslation
     */
    public function getTranslation($id_language = null)
    {
        $id_language = null === $id_language ? Engine_I18n::getLangId() : $id_language;
        if (!isset($this->Translations[$id_language])) {
            $translation = StandVideoTranslation::find($this->getId(), $id_language);
            if ($translation) {
                $this->Translations[$id_language] = $translation;
            }
        }

        return $this->Translations[$id_language];
    }

    public function parse_yturl($url)
    {
        $pattern = '#^(?:https?://)?'; // Optional URL scheme. Either http or https.
        $pattern .= '(?:www\.)?'; // Optional www subdomain.
        $pattern .= '(?:'; // Group host alternatives:
        $pattern .= 'youtu\.be/'; // Either youtu.be,
        $pattern .= '|youtube\.com'; // or youtube.com
        $pattern .= '(?:'; // Group path alternatives:
        $pattern .= '/embed/'; // Either /embed/,
        $pattern .= '|/v/'; // or /v/,
        $pattern .= '|/watch\?v='; // or /watch?v=,
        $pattern .= '|/watch\?.+&v='; // or /watch?other_param&v=
        $pattern .= ')'; // End path alternatives.
        $pattern .= ')'; // End host alternatives.
        $pattern .= '([\w-]{11})'; // 11 characters (Length of Youtube video ids).
        $pattern .= '(?:.+)?$#x'; // Optional other ending URL parameters.
        preg_match($pattern, $url, $matches);

        return (isset($matches[1])) ? $matches[1] : false;
    }
}
