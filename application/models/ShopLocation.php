<?php

/**
 * ShopLocation.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ShopLocation extends Table_ShopLocation
{
    public static function findOneByHash($hash)
    {
        return Doctrine::getTable('ShopLocation')->findOneByHash($hash);
    }

    /**
     * @param int        $id_shop_location
     * @param int        $id_language
     * @param null|mixed $tableName
     *
     * @return false|ShopLocation
     */
    public static function find($id_shop_location, $id_language = null, $tableName = null)
    {
        $id_language = null === $id_language ? Engine_I18n::getLangId() : $id_language;

        return Doctrine_Query::create()
            ->from('ShopLocation sl')
            ->leftJoin('sl.Translations t WITH t.id_language = ?', $id_language)
            ->where('sl.id_shop_location = ?', $id_shop_location)
            ->limit(1)
            ->execute()
            ->getFirst()
        ;
//            ->fetchOne();
    }
}
