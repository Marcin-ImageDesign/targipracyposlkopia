<?php

interface Briefcase_Service_Storage_Interface
{
    /**
     * @param int             $id_briefcase_type
     * @param Doctrine_Record $element
     * @param int             $id_namespace
     * @param null|mixed      $value
     *
     * @throws Exception
     *
     * @return Briefcase
     */
    public function addElement($id_briefcase_type, $element, $id_namespace = null, $value = null);

    public function getElementsIds($id_namespace = null);

    public function removeElement($id_briefcase_type, $element, $id_namespace = null);

    public function checkElementExists($id_briefcase_type, $element, $id_namespace = null);
}
