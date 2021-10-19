<?php

class AddressCountry extends Table_AddressCountry
{
    /**
     * @param mixed $id
     *
     * @return AddressCountry
     */
    public static function find($id, $id_language = null, $tableName = null)
    {
        return Doctrine::getTable('AddressCountry')->find($id);
    }

    public function getName()
    {
        return $this->name;
    }
}
