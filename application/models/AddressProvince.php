<?php

/**
 * AddressProvince.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AddressProvince extends Table_AddressProvince
{
    /**
     * @param mixed $id
     *
     * @return AddressProvince
     */
    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine::getTable('AddressProvince')->find($id);
    }

    public function getId()
    {
        return $this->id_address_province;
    }

    public function getName()
    {
        return $this->name;
    }
}