<?php

/**
 * @author Damian Jankowski
 */
class News_IndexController extends Engine_Controller_Frontend
{
    public $pages;
    /**
     * @var News
     */
    private $_news;

    // public function  preDispatch() {
    //     parent::preDispatch();

    //     );
    // }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender() && $this->_helper->layout->isEnabled()) {
            if ('hall' !== $this->_request->getActionName()) {
                $this->renderExhibitorsToPlaceholder('event/_section_nav.phtml', 'section-nav-content');
            }
        }
    }

    public function indexAction()
    {
//        $this->selectedEvent = Event::findOneByUriAndIdBaseUser($this->_getParam('event_uri'), $this->getSelectedBaseUser());

        $NewsQuery = Doctrine_Query::create()
            ->from('News n')
            ->innerJoin('n.Translations t')
            ->where('n.id_base_user = ? AND n.is_active = 1', $this->getSelectedBaseUser()->getId())
            ->addWhere('(n.publication_start IS NULL OR n.publication_start <= NOW())')
            ->addWhere('(n.publication_end IS NULL OR n.publication_end >= NOW())')
            ->addWhere('n.id_event = ?', $this->getSelectedEvent()->getId())
            ->addWhere('t.id_language = ?', Engine_I18n::getLangId())
            ->orderBy('n.publication_start DESC, n.created_at DESC')
        ;

        $pager = new Doctrine_Pager($NewsQuery, $this->_getParam('page', 1), 10);
        $NewsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;

        $this->view->zend_date = new Zend_Date();
        $this->view->newsList = $NewsList;
    }

    public function newsAction()
    {
        $this->_news = News::find($this->_getParam('news_uri'));

        //meta
        $this->view->headMeta()->setName('description', $this->_news->getMetatagDesc());
        $this->view->headMeta()->setName('keywords', $this->_news->getMetatagKey());
        $this->view->headTitle($this->_news->getMetatagTitle());

        $this->forward404Unless($this->_news, 'News NOT Exists (' . $this->_getParam('news_uri') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->_news),
            [$this->getSelectedBaseUser()->getId(), $this->_news->getId()]
        );

        $this->pages['news']['pages'][] = [
            'label' => $this->_news->getTitle(),
            'encodeUrl' => true,
            'route' => 'news',
            'params' => ['uri' => $this->_news->getUri()],
            'pages' => [],
        ];

//        $this->initCurrentPlugin( $this->news, ElementType::TYPE_NEWS );

        $this->view->placeholder('h1')->set($this->_news->getTitle());
        $this->view->news = $this->_news;
        $this->view->zend_date = new Zend_Date();
    }

    public function chatScheduleAction()
    {
        $NewsQuery = Doctrine_Query::create()
            ->from('News n')
            ->innerJoin('n.Translations t')
            ->where('n.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->addWhere('(n.publication_start IS NULL OR n.publication_start <= NOW())')
            ->addWhere('(n.publication_end IS NULL OR n.publication_end >= NOW())')
            ->addWhere('n.id_event = ?', $this->getSelectedEvent()->getId())
            ->addWhere('t.id_language = ?', Engine_I18n::getLangId())
            ->addwhere('chat_schedule = 1')
            ->orderBy('n.publication_start DESC, n.created_at DESC')
        ;

        $pager = new Doctrine_Pager($NewsQuery, $this->_getParam('page', 1), 10);
        $NewsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;

        $this->view->zend_date = new Zend_Date();
        $this->view->newsList = $NewsList;
    }
}
