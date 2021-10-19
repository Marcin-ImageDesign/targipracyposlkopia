<?php

/**
 * @author Robert Rogiński
 */
class News_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \News_Form_Admin_Filter|mixed
     */
    public $filter;
    /**
     * @var \News_Form_Admin_News_New|\News_Form_Admin_News_Edit|mixed
     */
    public $_newsForm;
    /**
     * @var \News|mixed
     */
    public $news;
    /**
     * @var News
     */
    private $_news;

    /**
     * @var News_Form_Element
     */
    private $_formNews;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_news'),
            'url' => $this->view->url([], 'news_admin'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $selectedOrder = $this->_session->news_list_order = $this->_hasParam('order') ? trim($this->_getParam('order', 'created_at DESC')) : (isset($this->_session->news_list_order) ? $this->_session->news_list_order : 'created_at DESC');
        $search = ['title' => '', 'is_active' => '', 'is_promoted' => ''];
        $languageChange = false;

        if ($this->_hasParam('clear')) {
            $this->_session->__unset('news_list_filter');
        } elseif (isset($search)) {
            $this->_session->news_list_filter = new stdClass();
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->news_list_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->news_list_filter->{$key}) ? $this->_session->news_list_filter->{$key} : $value);
            }
        }

        $this->filter = new News_Form_Admin_Filter();
        $this->filter->populate($search);

        $sort = $this->_getParam('created_at DESC');

        $newsListQuery = Doctrine_Query::create()
            ->from('News n')
            ->leftJoin('n.Translations t')
            ->where('n.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            // ->addwhere('chat_schedule = 0')
            ->orderBy('n.is_promoted, n.' . $selectedOrder)
        ;

        if ($this->hasSelectedEvent()) {
            $newsListQuery->addWhere('n.id_event = ?', $this->getSelectedEvent()->getId());
        }

        if (!empty($search['title'])) {
            if ($languageChange) {
                $newsListQuery->addWhere(
                    'n.title LIKE ? OR nlo.title LIKE ?',
                    ['%' . $search['title'] . '%', '%' . $search['title'] . '%']
                );
            } else {
                $newsListQuery->addWhere('n.title LIKE ?', '%' . $search['title'] . '%');
            }
        }

        if (mb_strlen($search['is_active']) > 0) {
            $newsListQuery->addWhere('n.is_active = ?', $search['is_active']);
        }

        if (mb_strlen($search['is_promoted']) > 0) {
            $newsListQuery->addWhere('n.is_promoted = ?', $search['is_promoted']);
        }

        $pager = new Doctrine_Pager($newsListQuery, $this->_getParam('page', 1), 50);
        $newsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->newsList = $newsList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('News list'));
    }

    public function newAction()
    {
        $this->_news = new News();
        $this->_news->BaseUser = $this->getSelectedBaseUser();
        $this->_news->hash = $this->engineUtils->getHash();
        $this->_news->is_active = true;

        $this->setSelectedLanguage();

        $eventId = null;
        if ($this->hasSelectedEvent()) {
            $eventId = ($this->getSelectedEvent()->getId());
        }

        $this->_newsForm = new News_Form_Admin_News_New($this->_news, $eventId);

        $this->newsForm();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New news'));
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_news-new'),
            'url' => $this->view->url(),
        ];
    }

    public function editAction()
    {
        $this->_news = News::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->_news, 'News NOT Exists, hash: (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->_news),
            [$this->getSelectedBaseUser()->getId(), $this->_news->getId()]
        );

        $eventId = null;
        if ($this->hasSelectedEvent()) {
            $eventId = ($this->getSelectedEvent()->getId());
        }
        $this->_newsForm = new News_Form_Admin_News_Edit($this->_news, $eventId);
        $this->newsForm();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit news'));
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_news-edit'),
            'url' => $this->view->url(),
        ];
    }

    /**
     * @return News_Form_Element
     */
    // private function getFormNews()
    // {
    //     $formNews = new News_Form_Element( $this->news );

    //     // edxytujemy wersję językową
    //     if ($this->getSelectedLanguage()->code !== Language::DEFAULT_LANGUAGE_CODE && !isset($this->news->NewsLanguageOne)) {
    //         $this->news->NewsLanguageOne = new NewsLanguage();
    //         $this->news->NewsLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
    //     }

    //     $params = array('uriFilter' => true);
    //     if( false === $this->news->isNew() ) {
    //         $params[] = array('id_news', '!=', $this->news->getId());
    //     }

    //     if( isset( $this->news->NewsLanguageOne)) {
    //         $model = 'NewsLanguage';
    //         $params[] = array('id_base_user_language', '=', $this->news->NewsLanguageOne->id_base_user_language);
    //     } else {
    //         $model = 'News';
    //         $params[] = array('id_base_user', '=', $this->getSelectedBaseUser()->getId());
    //     }

    //     $vAlreadyTaken = new Engine_Validate_AlreadyTaken($model, 'uri', $params);
    //     $formNews->getSubForm('header')->getElement('title')->addValidator($vAlreadyTaken);

    //     return $formNews;
    // }

    //   private function newsGetRequest() {
    //       $this->news->is_active = (bool) $this->formNews->aside->is_active->getValue();
    //       $this->news->is_promoted = (bool) $this->formNews->aside->is_promoted->getValue();
    //       $this->news->is_metatag = (bool) $this->formNews->seo->is_metatag->getValue();
    //       $this->news->setTitle($this->formNews->header->title->getValue());
    //       $this->news->setUri($this->engineUtils->getFriendlyUri($this->news->getTitle()));

    //       $this->news->setPublicationStart( $this->formNews->main->publication_start->getValue() );
    //       $this->news->setPublicationEnd( $this->formNews->main->publication_end->getValue() );
    //       $this->news->setLead($this->formNews->main->lead->getValue());
    //       $this->news->setText($this->formNews->main->text->getValue());

    //       if( $this->news->is_metatag ) {
    //           $this->news->setMetatagTitle( $this->formNews->seo->metatag_title->getValue() );
    //           $this->news->setMetatagKey( $this->formNews->seo->metatag_key->getValue() );
    //           $this->news->setMetatagDesc( $this->formNews->seo->metatag_desc->getValue() );
    //       }

    //       // zapis obrazka
    // $upload = new Zend_File_Transfer_Adapter_Http();
    // $files = $upload->getFileInfo();

    // if( !empty( $files ) && isset( $files['image'] ) && $files['image']['error'] != 4 ){
    // 	$this->news->deleteImage();

    // 	$tmp_name = $files['image']['tmp_name'];
    // 	$imageInfo = getimagesize($tmp_name);
    //           $this->news->image_ext = $this->engineUtils->getFileExt($files['image']['name']);

    //           $engineVariable = Engine_Variable::getInstance();
    //           $thumbWidth = $engineVariable->getVariable(Variable::NEWS_IMAGE_THUMB_WIDTH, $this->getSelectedBaseUser()->getId());
    //           $thumbHeight = $engineVariable->getVariable(Variable::NEWS_IMAGE_THUMB_HEIGHT, $this->getSelectedBaseUser()->getId());

    //           $image = Engine_Image::factory();
    //           $image->open( $tmp_name );
    //           $image->resizeIfBigger( News::IMAGE_WIDTH , News::IMAGE_HEIGHT );
    //           $image->save( $this->news->getRelativeImage() );

    //           $image->open( $tmp_name );
    //           $image->resizeIfBigger( $thumbWidth , $thumbHeight );
    //           $image->save( $this->news->getRelativeImageThumb() );
    // }
    //   }

    public function statusAction()
    {
        $this->news = News::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->news, 'News NOT Exists (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->news),
            [$this->getSelectedBaseUser()->getId(), $this->news->getId()]
        );

        $this->news->is_active = !$this->news->is_active;
        $this->news->save();

        $this->_redirector->gotoRouteAndExit([], 'news_admin');
    }

    public function deleteAction()
    {
        $this->news = News::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->news, 'News NOT Exists (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->news),
            [$this->getSelectedBaseUser()->getId(), $this->news->getId()]
        );

        $this->news->delete();

        $this->_flash->success->addMessage('Item has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'news_admin');
    }

    public function deleteImageAction()
    {
        $this->news = News::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->news, 'News NOT Exists (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->news),
            [$this->getSelectedBaseUser()->getId(), $this->news->getId()]
        );

        $this->news->deleteImage();
        $this->news->save();

        $this->_flash->success->addMessage('Item has been deleted');
        $this->_redirector->gotoRouteAndExit(['hash' => $this->news->hash], 'news_admin_edit');
    }

    public function chatScheduleAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Harmonogram Czatów'));

        $chatScheduleList = Doctrine_Query::create()
            ->select('t.title, t.lead, t.text, n.publication_start, n.id_image, n.hash, i.file_path, i.id_image')
            ->from('News n')
            ->innerJoin('n.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->innerJoin('n.Image i')
            ->where('chat_schedule = 1')
            ->execute()->toArray();

        $this->view->chatScheduleList = $chatScheduleList;
    }

    private function newsForm()
    {
        if ($this->_request->isPost() && $this->_newsForm->isValid($this->_request->getPost())) {
            $this->_news->save();
            if (isset($_FILES['image']) && 0 === $_FILES['image']['error']) {
                $image = Service_Image::createImage(
                    $this->_news,
                    [
                        'type' => $_FILES['image']['type'],
                        'name' => $_FILES['image']['name'],
                        'source' => $_FILES['image']['tmp_name'], ]
                );

                $this->_news->id_image = $image->getId();
                $this->_news->save();
            }
            if (isset($_FILES['image_home']) && 0 === $_FILES['image_home']['error']) {
                $image_home = Service_Image::createImage(
                    $this->_news,
                    [
                        'type' => $_FILES['image_home']['type'],
                        'name' => $_FILES['image_home']['name'],
                        'source' => $_FILES['image_home']['tmp_name'], ]
                );

                $this->_news->id_image_home = $image_home->getId();
                $this->_news->save();
            }
            $this->_flash->success->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit(['hash' => $this->_news->hash], 'news_admin_edit');
        }

        $this->_helper->viewRenderer('form');
        $this->view->newsForm = $this->_newsForm;
    }
}
