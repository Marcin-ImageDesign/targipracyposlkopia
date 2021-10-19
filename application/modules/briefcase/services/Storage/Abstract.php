<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 03.10.13
 * Time: 08:49
 * To change this template use File | Settings | File Templates.
 */
abstract class Briefcase_Service_Storage_Abstract implements Briefcase_Service_Storage_Interface
{
//    abstract public function addElement($id_briefcase_type, $element, $id_namespace = null, $value = null);
//
//    abstract public function getElementsIds($id_namespace = null);
//
//    abstract public function removeElement($id_briefcase_type, $element, $id_namespace = null);
//
//    abstract public function checkElementExists($id_briefcase_type, $element, $id_namespace = null);

    public function getElementsIdsByType($idBriefcaseType, $id_namespace = null)
    {
        $elements = $this->getElementsIds($id_namespace);

        return (array) @$elements[$idBriefcaseType];
    }
}
