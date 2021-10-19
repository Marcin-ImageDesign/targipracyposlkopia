<?php

class Briefcase_Service_Storage_Adapter_Database extends Briefcase_Service_Storage_Abstract
{
    /**
     * @var User
     */
    private $userAuth = false;

    public function __construct()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
    }

    public function getElementsIds($id_namespace = null)
    {
        $briefcaseTypeQuery = Doctrine_Query::create()
            ->from('BriefcaseType bt')
            ->innerJoin('bt.Briefcase b')
            ->where('b.id_user = ?', $this->userAuth->getId())
        ;

        if (null !== $id_namespace) {
            $briefcaseTypeQuery->addWhere('b.id_namespace = ?', $id_namespace);
        }

        $briefcaseTypeList = $briefcaseTypeQuery->execute();

        $elementsIds = [];
        foreach ($briefcaseTypeList as $briefcaseType) {
            $elementsIds[$briefcaseType->getId()] = [];
            foreach ($briefcaseType->Briefcase as $briefcase) {
                $elementsIds[$briefcaseType->getId()][$briefcase->getId()] = $briefcase->id_element;
            }
        }

        return $elementsIds;
    }

    /**
     * @param int $id_briefcase_type
     * @param int $element
     * @param int $id_namespace
     * @param null|mixed $value
     *
     * @return Briefcase
     * @throws Exception
     */
    public function addElement($id_briefcase_type, $element, $id_namespace = null, $value = null)
    {
        $briefcaseType = BriefcaseType::find($id_briefcase_type);
        if (!$briefcaseType) {
            throw new Exception('BriefcaseType NOT Found id:(' . $id_briefcase_type . ')');
        }

        $checkBriefcaseExists = Briefcase::checkUserHasElement(
            $this->userAuth,
            $briefcaseType->getId(),
            $element,
            $id_namespace
        );

        if ($checkBriefcaseExists) {
            return false;
        }

        $utils = Engine_Utils::getInstance();
        $briefcase = new Briefcase();
        $briefcase->hash = $utils->getHash();
        $briefcase->is_active = true;
        $briefcase->User = $this->userAuth;
        $briefcase->BriefcaseType = $briefcaseType;
        $briefcase->id_element = $element;
        $briefcase->id_namespace = $id_namespace;
        $briefcase->save();

        return $briefcase;
    }

    public function removeElement($id_briefcase_type, $element, $id_namespace = null)
    {
        $element = $this->_getElement($id_briefcase_type, $element, $id_namespace);

        if (!$element) {
            return false;
        }

        $element->delete();
    }

    public function checkElementExists($id_briefcase_type, $element, $id_namespace = null)
    {
        return (bool) $this->_getElement($id_briefcase_type, $element, $id_namespace);
    }

    private function _getElement($id_briefcase_type, $element, $id_namespace = null)
    {
        $query = Doctrine_Query::create()
            ->from('Briefcase b')
            ->where(
                'b.id_user = ? AND b.id_briefcase_type = ? AND b.id_element = ?',
                [$this->userAuth->getId(), $id_briefcase_type, $element]
            )
            ->limit(1)
        ;

        if (null !== $id_namespace) {
            $query->addWhere('b.id_namespace = ?', $id_namespace);
        }

        return $query->execute()->getFirst();
    }
}
