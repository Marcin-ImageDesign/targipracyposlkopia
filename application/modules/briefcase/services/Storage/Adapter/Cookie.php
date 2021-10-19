<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 01.10.13
 * Time: 10:41
 * To change this template use File | Settings | File Templates.
 */
class Briefcase_Service_Storage_Adapter_Cookie extends Briefcase_Service_Storage_Abstract
{
    protected $_elements = [];

    private static $_cookieName = 'product';

    public function __construct()
    {
        $this->_elements = $this->_getElements();
    }

    public function getElementsIds($id_namespace = null)
    {
        $ns = $this->_getNamespace($id_namespace);

        return (array) $this->_elements[$ns];
    }

    /**
     * @param int             $id_briefcase_type
     * @param Doctrine_Record $element
     * @param int             $id_namespace
     * @param null|mixed      $value
     *
     * @throws Exception$id_element
     *
     * @return Briefcase
     */
    public function addElement($id_briefcase_type, $element, $id_namespace = null, $value = null)
    {
        $ns = $this->_getNamespace($id_namespace);
        $this->_elements[$ns][$id_briefcase_type][$element] = $element;
        $this->_setElements($this->_elements);
    }

    public function removeElement($id_briefcase_type, $element, $id_namespace = null)
    {
        $ns = $this->_getNamespace($id_namespace);
        if (array_key_exists($element, $this->_elements[$ns][$id_briefcase_type])) {
            unset($this->_elements[$ns][$id_briefcase_type][$element]);
            $this->_setElements($this->_elements);

            return true;
        }

        return false;
    }

    public function checkElementExists($id_briefcase_type, $element, $id_namespace = null)
    {
        $ns = $this->_getNamespace($id_namespace);
        if (isset($this->_elements[$ns][$id_briefcase_type])) {
            return array_key_exists($element, $this->_elements[$ns][$id_briefcase_type]);
        }

        return false;
    }

    private function _getNamespace($id_namespace = null)
    {
        return (null === $id_namespace) ? 'ns_default' : 'ns_' . $id_namespace;
    }

    private function _getElements()
    {
        $request = new Zend_Controller_Request_Http();
        $cookie = $request->getCookie(self::$_cookieName);

        return unserialize($cookie);
    }

    private function _setElements($elements)
    {
        $cookieValue = serialize($elements);
        setcookie(self::$_cookieName, $cookieValue, time() + 7200, '/');
    }
}
