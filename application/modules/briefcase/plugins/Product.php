<?php

/**
 * Description of Document.
 *
 * @author robert.roginski
 */
class Briefcase_Plugin_Product implements Briefcase_Service_Interface
{
    /**
     * @var Zend_View
     */
    private $view;

    public function getView($elementsIds)
    {
        $this->view = clone Zend_Registry::get('Zend_View');
        $front = Zend_Controller_Front::getInstance();
        $this->view->addScriptPath($front->getModuleDirectory('briefcase') . DS . 'templates');

        $html = '';
        if (!empty($elementsIds)) {
            $eventStandProductList = Doctrine_Query::create()
                ->from('StandProduct esf')
                ->whereIn('esf.id_stand_product', array_values($elementsIds))
                ->execute()
            ;

            $this->view->eventStandProductList = $eventStandProductList;
            $this->view->elementsIds = $elementsIds;

            if ($eventStandProductList->count() > 0) {
                $html = $this->view->render('_brefcase_plugin_product.phtml');
            }
        }

        return $html;
    }

    public function getElement($element)
    {
        return ExhibStandFile::find($element);
    }
}
