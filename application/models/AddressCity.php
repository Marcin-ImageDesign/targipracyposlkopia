<?php

/**
 * AddressCity.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AddressCity extends Table_AddressCity
{
    /**
     * @param mixed $id
     *
     * @return AddressCity
     */
    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine::getTable('AddressCity')->find($id);
    }

    /**
     * @param mixed $name
     *
     * @return AddressCity
     */
    public static function findOneByProvinceAndName(AddressProvince $province, $name)
    {
        return Doctrine_Query::create()
            ->from('AddressCity ac')
            ->where('ac.id_address_province = ?', $province->getId())
            ->addWhere('ac.name = ?', mb_strtolower($name, 'UTF-8'))
            ->execute()
            ->getFirst()
        ;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
