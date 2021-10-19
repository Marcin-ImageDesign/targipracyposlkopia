<?php

/**
 * Description of Document.
 *
 * @author robert.roginski
 */
class Briefcase_Plugin_Document implements Briefcase_Service_Interface
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
            $eventStandFilesList = Doctrine_Query::create()
                ->from('ExhibStandFile esf')
                ->whereIn('esf.id_exhib_stand_file', array_values($elementsIds))
                ->execute()
            ;

            $this->view->eventStandFilesList = $eventStandFilesList;
            $this->view->elementsIds = $elementsIds;

            if ($eventStandFilesList->count() > 0) {
                $html = $this->view->render('_brefcase_plugin_document.phtml');
            }
        }

        return $html;
    }

    public function getElement($element)
    {
        return ExhibStandFile::find($element);
    }
}
