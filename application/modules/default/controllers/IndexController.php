<?php

class IndexController extends Engine_Controller_Frontend
{
    /**
     * @var \Form_Contact|mixed
     */
    public $contactForm;

    public function postDispatch()
    {
        parent::postDispatch();

        if (!$this->_helper->viewRenderer->getNoRender() && $this->_helper->layout->isEnabled()) {
            $this->view->headMeta()->setName('og:image', '/_images/frontend/default/fb_img.png?2');
        }
    }

    public function init()
    {
    }

    public function indexAction()
    {
        if ($this->getSelectedEvent()->hasHomePageUrl()) {
            $this->_redirector->gotoUrlAndExit($this->getSelectedEvent()->getHomePageUrl());
        }

        $zDate = new Zend_Date();
        // check if selected event was started
        $showEvent = $zDate->isLater($this->getSelectedEvent()->getDateStart());

        // check if cookie is set and what is it's value
        if (isset($_COOKIE['Cec6hi3piu5Du5iezhoh3aithohrae7ziph2_glowna'])) {
            $showEvent = 0 !== $_COOKIE['Cec6hi3piu5Du5iezhoh3aithohrae7ziph2_glowna'];
        }

        // generowanie listy przyszłych targów
        $futureEvents = Event::findPlanned();
        $this->view->futureEvents = $futureEvents;
        // generowanie listy zakończonych targów
        $pastEvents = Event::findEnded();
        $this->view->pastEvents = $pastEvents;

        // żeby widok wiedział, że event jest włączony
        $this->view->showEvent = $showEvent;

        //pobieramy nazwe routingu
        $routeName = $this->getRouteName();

        // pobieranie listy artykułów strony głównej
        $newsHomePage = Doctrine_Query::create()
            ->from('News n')
            ->innerJoin('n.Translations t')
            ->where('n.on_homepage = 1')
            ->addWhere('n.id_event = ?', $this->getSelectedEvent()->getId())
            ->orderBy('rand()')
            ->limit(1)
            ->execute()
            ->getFirst();

        $sideBanners = $this->getSelectedEvent()->getBannersByGroup(EventBannerGroup::BANNER_DEFAULT_GROUP);
        $background_data = EventBackground::findOneByName('standard_hall');

        // odczyt pozycji, wielkości i nazwy grup banerów
        $data_banner = (array)@json_decode($background_data['data_banner'], true);

        foreach ($data_banner as $key => $banner) {
            $banners = $this->getSelectedEvent()->getBannersByGroup($banner['name']);
            $data_banner[$key]['banners'] = $banners;
        }

        $this->view->data_banner = $data_banner;

        $this->view->background_data = $background_data;
        $this->view->sideBanners = $sideBanners;
        $this->view->newsHomePage = $newsHomePage;
        $this->view->is_skip_reception = Engine_Variable::getInstance()->getVariable(Variable::SKIP_RECEPTION);
        // if conditions above are met - show event

        if ($showEvent) {
            $homePage = $this->getSelectedEvent()->getHomePage($this->getSelectedLanguage());
            $this->view->pageData = $homePage->getPageData();

            //na podstawie kolumny main_page_style, ustalamy do którego widoku strony głównej idziemy
            $main_page_style = $this->getSelectedBaseUser()->getMainPageStyle();
            $main_page_style = !empty($main_page_style) ? $main_page_style : 'box';

            $this->_helper->viewRenderer('index-' . $main_page_style);
        } else {
            $this->contactForm = new Form_Contact([
                'baseUser' => $this->getSelectedBaseUser(),
            ]);

            $this->view->contactForm = $this->contactForm;

            if ($this->_request->isPost() && $this->contactForm->isValid($this->_request->getPost())) {
                $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], $routeName . '-contact-thanks');
            }

            $this->_helper->viewRenderer('contact');
        }
    }

    public function chatAction()
    {
        $this->_redirector->gotoUrlAndExit(CHAT_URL);
    }

    public function contactThanksAction()
    {
        // generowanie listy przyszłych targów
        // tutaj też bo po wyslaniu formularza walilo notice'ami na stronie
        $futureEvents = Event::findPlanned();
        $this->view->futureEvents = $futureEvents;
        // generowanie listy zakończonych targów
        $pastEvents = Event::findEnded();
        $this->view->pastEvents = $pastEvents;
    }
}
