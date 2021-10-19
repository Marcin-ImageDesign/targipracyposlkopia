<?php

class Briefcase_Service_Storage_Adapter_Session extends Briefcase_Service_Storage_Abstract
{
    /**
     * @var Zend_Session_Namespace
     */
    private $_session;

    /**
     * @var array
     */
    private $_elements = [];

    public function __construct()
    {
        $this->_session = new Zend_Session_Namespace('briefcase');
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
        $this->_elements[$ns][$id_briefcase_type][$element] = $value;
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
        return $this->_session->__get('elements');
    }

    private function _setElements($elements)
    {
        return $this->_session->__set('elements', $elements);
    }
}
