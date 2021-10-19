<?php

class Event_AdminStandViewImageController extends Engine_Controller_Admin
{
    /**
     * @var ExhibStandViewImage
     */
    private $_standViewImage;

    /**
     * @var Event_Form_Admin_StandViewImage
     */
    private $_standViewImageForm;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin-stand-view-image/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $standViewImageQuery = Doctrine_Query::create()
            ->from('ExhibStandViewImage esvi')
            ->innerJoin('esvi.StandLevel sl')
            ->orderBy('esvi.id_stand_level ASC, esvi.name ASC')
        ;

        $pager = new Doctrine_Pager($standViewImageQuery, $this->_getParam('page', 1), 50);
        $standViewImageList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_stand-view-image-list'));
        $this->view->standViewImageList = $standViewImageList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
    }

    public function newAction()
    {
        $standLevel = StandLevel::find(StandLevel::LEVEL_STANDARD);
        $this->_standViewImage = new ExhibStandViewImage();
        $this->_standViewImage->hash = Engine_Utils::_()->getHash();
        $this->_standViewImage->BaseUser = $this->getSelectedBaseUser();
        $this->_standViewImage->StandLevel = $standLevel;
        $this->_standViewImage->is_active = true;
        $this->_standViewImage->is_public = true;
        $this->_standViewImage->setDataIcon([]);
        $this->_standViewImage->setDataBanner([]);
        $this->_standViewImage->setDataStand([]);

        $this->_formStandViewImage();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_stand-view_image_new'));
    }

    public function editAction()
    {
        $this->_standViewImage = ExhibStandViewImage::findOneByHash($this->getParam('hash'));
        $this->forward404Unless($this->_standViewImage);
//        $this->_standViewImage->StandLevel;
//        $this->_standViewImage->refreshRelated('StandLevel');
//        $this->_standViewImage->refreshRelated('Image');
//        $this->_standViewImage->refreshRelated('BaseUser');

//        var_dump($this->_standViewImage->toArray(1));
//        exit();

        $this->_formStandViewImage();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_stand-view_image_edit'));
    }

    public function publicAction()
    {
        $this->_standViewImage = ExhibStandViewImage::findOneByHash($this->getParam('hash'));
        $this->forward404Unless($this->_standViewImage);

        $this->_standViewImage->is_public = !$this->_standViewImage->is_public;
        $this->_standViewImage->save();

        $this->_redirector->gotoRouteAndExit([], 'event_admin-stand-view-image');
    }

    public function getBannerAction()
    {
        $key = time();
        $banner = new Event_Form_Admin_StandViewImage_Banner([
            'data' => [],
            'key' => 'new',
        ]);
        $banner->setElementsBelongTo('StandViewImage[data_banner][' . $key . ']');

        $this->jsonResult['result'] = true;
        $this->jsonResult['html'] = $banner->render();
    }

    private function _formStandViewImage()
    {
        $this->_standViewImageForm = new Event_Form_Admin_StandViewImage(['model' => $this->_standViewImage]);
        if ($this->_request->isPost() && $this->_standViewImageForm->isValid($this->_request->getPost())) {
            $this->_standViewImage->save();
            $this->_flash->success->addMessage($this->view->translate('label_form_save_success'));
            $this->_redirector->gotoRouteAndExit();
        }

        $this->_helper->viewRenderer('form');
        $this->view->form = $this->_standViewImageForm;
        $this->view->standViewImage = $this->_standViewImage;
    }
}
