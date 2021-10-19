<?php

class Briefcase_Service_Model
{
    /**
     * @var Briefcase_Service_Storage_Abstract
     */
    private $storage;

    /**
     * @param Briefcase_Service_Storage_Interface $storage
     */
    public function __construct(Briefcase_Service_Storage_Abstract $storage)
    {
        $this->storage = $storage;
    }

    public function getAll($id_namespace = null)
    {
        $elementsIds = $this->storage->getElementsIds($id_namespace);

        $briefcaseElements = [];
        foreach ($elementsIds as $id_briefcase_type => $elements) {
            $briefcaseType = BriefcaseType::findOneByIdAndIsActive($id_briefcase_type);
            if ($briefcaseType) {
                $briefcasePlugin = new $briefcaseType->plugin();

                $view = $briefcasePlugin->getView($elements);
                if (!empty($view)) {
                    $briefcaseElements[$briefcaseType->getId()] = [
                        'briefcaseType' => $briefcaseType->toArray(0),
                        'view' => $view,
                    ];
                }
            }
        }

        return $briefcaseElements;
    }

    public function getElementsByType(BriefcaseType $briefcaseType, $id_namespace = null)
    {
        return $this->storage->getElementsIdsByType($briefcaseType->getId(), $id_namespace);
    }

    public function addElement($id_briefcase_type, $element, $value, $id_namespace = null)
    {
        return $this->storage->addElement($id_briefcase_type, $element, $value, $id_namespace);
    }

    public function removeElement($id_briefcase_type, $element, $id_namespace = null)
    {
        return $this->storage->removeElement($id_briefcase_type, $element, $id_namespace);
    }

    public function checkElementExists($id_briefcase_type, $element, $id_namespace = null)
    {
        return $this->storage->checkElementExists($id_briefcase_type, $element, $id_namespace);
    }
}
