<?php

/**
 * Text.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Text extends Table_Text
{
    /**
     * @param $uri
     * @param null $id_language
     *
     * @return false|Text
     */
    public static function findOneByUri($uri, $id_language = null)
    {
        $id_language = null !== $id_language ? $id_language : Engine_I18n::getLangId();

        return Doctrine_Query::create()
            ->from(__CLASS__ . ' t')
            ->innerJoin('t.Translations tt')
            ->where('tt.id_language = ?', $id_language)
            ->addWhere('tt.uri = ?', $uri)
            ->limit(1)
            ->fetchOne()
        ;
    }

    /**
     * @param $hash
     * @param null $id_language
     *
     * @return false|Text
     */
    public static function findOneByHash($hash, $id_language = null)
    {
        $id_language = null !== $id_language ? $id_language : Engine_I18n::getLangId();

        return Doctrine_Query::create()
            ->from(__CLASS__ . ' t')
            ->innerJoin('t.Translations tt')
            ->where('tt.id_language = ?', $id_language)
            ->addWhere('t.hash = ?', $hash)
            ->limit(1)
            ->fetchOne()
        ;
    }
}
