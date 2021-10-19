<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stand.
 *
 * @author marcin
 */
class Briefcase_Plugin_Stand implements Briefcase_Service_Interface
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
            $eventStandsList = Doctrine_Query::create()
                ->from('ExhibStand es')
                ->whereIn('es.id_exhib_stand', array_values($elementsIds))
                ->execute()
            ;

            $this->view->eventStandsList = $eventStandsList;
            $this->view->elementsIds = $elementsIds;

            if ($eventStandsList->count() > 0) {
                $html = $this->view->render('_brefcase_plugin_stand.phtml');
            }
        }

        return $html;
    }

    public function getElement($element)
    {
        return ExhibStand::find($element);
    }
}
