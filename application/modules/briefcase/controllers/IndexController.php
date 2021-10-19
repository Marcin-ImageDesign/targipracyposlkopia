<?php

class Briefcase_IndexController extends Engine_Controller_Frontend
{
    /**
     * @var \BriefcaseType|mixed
     */
    public $briefcaseType;
    /**
     * @var Briefcase_Service_Model
     */
    private $_briefcaseService;

    private $_exhibParticipant;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_briefcaseService = Zend_Registry::get('BriefcaseService');

        if ($this->_helper->layout->isEnabled()) {
            $this->_breadcrumb[] = [
                'url' => $this->view->url(),
                'label' => $this->view->translate($this->addActualBreadcrumb()),
            ];
        }

        $this->_exhibParticipant = $this->getExhibParticipant();
        $this->view->exhibParticipant = $this->_exhibParticipant;

        $this->getResponse()->setHeader('Cache-Control', 'no-cache, must-revalidate, post-check=0, pre-check=0', true);
        $this->getResponse()->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true);
        $this->getResponse()->setHeader('Pragma', 'no-cache', true);
    }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender() && $this->_helper->layout->isEnabled()) {
            if ($this->hasSelectedEvent()) {
                if ($this->userAuth) {
                    $this->view->renderToPlaceholder('user/_section_nav.phtml', 'section-nav-content');
                } else {
                    $this->renderNewsToPlaceholder('news/_section_nav.phtml', 'section-nav-content');
                }
            }
        }
    }

    public function indexAction()
    {
        $id_namespace = null;
        if ($this->hasSelectedEvent()) {
            $id_namespace = $this->getSelectedEvent()->getId();
        }

        $briefcaseList = $this->_briefcaseService->getAll($id_namespace);

        $this->view->briefcaseList = $briefcaseList;
    }

    public function addElementAction()
    {
        $this->briefcaseType = BriefcaseType::find($this->_getParam('id_briefcase_type'));
        $this->forward403Unless($this->briefcaseType, 'Briefcase Not Found (' . $this->_getParam('id_briefcase_type') . ')');

        $element = $this->_getParam('element');
        $this->forward403Unless($element, 'Element is required');

        $brefcase = $this->_briefcaseService->addElement(
            $this->briefcaseType->getId(),
            $element,
            $this->_getParam('namespace', null)
        );

        // odpowiedx dla flash
        if ($this->_hasParam('isFlashHttpRequest')) {
            echo '&ret=true';
            exit();
        }
        if (!$this->_request->isXmlHttpRequest()) {
            // $this->_flash->success->addMessage('Save successfully completed');
            if ($this->_hasParam('return')) {
                $return = $this->_getParam('return');
                $this->_redirector->gotoUrlAndExit($return);
            }

            $this->_redirector->gotoRouteAndExit([], 'briefcase');
        } else {
            $this->jsonResult['result'] = true;
            $this->jsonResult['link'] = $this->view->url(
                ['element' => $element,
                    'id_briefcase_type' => $this->_getParam('id_briefcase_type'),
                    'namespace' => $this->_getParam('namespace'),
                ],
                'briefcase_remove-element'
            );
            $this->jsonResult['title'] = $this->view->translate('label_briefcase_remove-from-briefcase');
        }
    }

    public function removeElementAction()
    {
        $this->briefcaseType = BriefcaseType::find($this->_getParam('id_briefcase_type'));
        $this->forward403Unless($this->briefcaseType, 'Briefcase Not Found (' . $this->_getParam('id_briefcase_type') . ')');

        $element = $this->_getParam('element');
        $this->forward403Unless($element, 'Element is required');

        $brefcase = $this->_briefcaseService->removeElement(
            $this->briefcaseType->getId(),
            $element,
            $this->_getParam('namespace', null)
        );

        // odpowiedx dla flash
        if ($this->_hasParam('isFlashHttpRequest')) {
            echo '&ret=true';
            exit();
        }
        if ($this->_request->isXmlHttpRequest()) {
            $this->jsonResult['result'] = true;
            $this->jsonResult['remove'] = 1;
            $this->jsonResult['success'] = true;
            $this->jsonResult['link'] = $this->view->url([
                'id_briefcase_type' => $this->_getParam('id_briefcase_type'),
                'element' => $this->_getParam('element'),
                'namespace' => $this->_getParam('namespace', null),
            ], 'briefcase_add-element');
            $this->jsonResult['title'] = $this->view->translate('label_briefcase_add-to-briefcase');
        //        $this->_redirector->gotoRouteAndExit( array(), 'briefcase' );
        } else {
            if ($this->_hasParam('return')) {
                $return = $this->_getParam('return');
                $this->_redirector->gotoUrlAndExit($return, [
                    'prependBase' => false,
                ]);
            }

            $this->_redirector->gotoRouteAndExit([], 'briefcase');
        }
    }
}
