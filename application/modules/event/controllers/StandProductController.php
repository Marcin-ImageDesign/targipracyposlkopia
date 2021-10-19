<?php

class Event_StandProductController extends Engine_Controller_Frontend
{
    /**
     * @var ExhibStand
     */
    private $_exhibStand;

    /**
     * @var StandProduct
     */
    private $_standProduct;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_exhibStand = ExhibStand::findOneByUri($this->_getParam('stand_uri'), $this->getSelectedBaseUser(), $this->getSelectedEvent()->getId(), $this->_getParam('hall_uri'));
        $this->forward404Unless($this->_exhibStand, 'Stand not exist, uri: (' . $this->_getParam('stand_uri') . '), id_base_user: (' . $this->getSelectedBaseUser()->getId() . ')');

        $this->_breadcrumb[] = [
            'url' => $this->view->url([
                'event_uri' => $this->getSelectedEvent()->getUri(),
                'stand_uri' => $this->_exhibStand->getUri(),
                'hall_uri' => $this->_exhibStand->EventStandNumber->EventHallMap->uri,
            ], 'event_stand'),
            'label' => $this->_exhibStand->getName(),
        ];

        $this->_breadcrumb[] = [
            'url' => $this->view->url([
                'event_uri' => $this->getSelectedEvent()->getUri(),
                'stand_uri' => $this->_exhibStand->getUri(),
                'hall_uri' => $this->_exhibStand->EventStandNumber->EventHallMap->uri,
            ], 'event_stand-offer_index'),
            'label' => $this->view->translate('breadcrumb_event_stand-product_product-list'),
        ];

        // RHINO
        $this->view->rhino_url = "";

        $this->addContactForm(1);
        //ustawiamy aktywną halę
        $this->setActiveEventHall($this->_exhibStand->EventStandNumber->id_event_hall_map);
    }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender()) {
            $this->view->exhibStand = $this->_exhibStand;
            $this->view->cookieChatAllow = true;
            if ($this->_helper->layout->isEnabled()) {
                $this->view->renderToPlaceholder('stand/_section_nav.phtml', 'section-nav-content');
            }
        }
    }

    public function indexAction()
    {
        $offerPassword = $this->getSelectedEvent()->offer_password;

        if ($offerPassword && ($this->_session->offerPassword != $offerPassword)) {
            $this->_session->offerPasswordRedirect = $this->view->url([
                'event_uri' => $this->getSelectedEvent()->getUri(),
                'stand_uri' => $this->_exhibStand->getUri(),
                'hall_uri' => $this->_exhibStand->EventStandNumber->EventHallMap->uri,
            ], 'event_stand-offer_index');

            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'event_offer_login');
        }

        //metatagi
        $this->view->headMeta()->setName('description', $this->_exhibStand->getName() . ' - ' . $this->_exhibStand->getShortInfo());
        $this->view->headTitle($this->_exhibStand->getName() . ' - ' . $this->view->translate('meta_products'));

        $standProductQuery = Doctrine_Query::create()
            ->from('StandProduct sp')
            ->innerJoin('sp.Translations t')
            ->where('sp.is_active = 1 AND sp.id_exhib_stand = ?', $this->_exhibStand->getId())
            ->addWhere('t.id_language = ?', Engine_I18n::getLangId())
            ->orderBy('sp.is_promotion DESC, t.name ASC')
        ;

        // Oferty promocyjne
        if ($this->getParam('promoted')) {
            $standProductQuery->addWhere('sp.is_promotion = 1');
        }

        $pager = new Doctrine_Pager($standProductQuery, $this->getParam('page', 1), 1000);
        $standProductList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->id_briefcase_type = BriefcaseType::TYPE_BRIEFCASE_PRODUCT;

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_PRODUCT_VIEWLIST, $this->_exhibStand->getId(), $this->getSelectedEvent()->getId());

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->standProductList = $standProductList;
        $this->view->show_prices = Engine_Variable::getInstance()->getVariable(Variable::SHOW_PRICES, $this->getSelectedBaseUser()->getId());
    }

    public function applyAction()
    {
        $this->_standProduct = StandProduct::findOneByHash($this->_getParam('product_hash'));
        $this->forward404Unless($this->_standProduct, 'StandProduct not found, hash: (' . $this->_getParam('product_hash') . ')');

        if ($this->_standProduct->isFormTarget()) {
            // Statystyki

            //sprawdz czy nie bot
            $delay = Engine_Variable::getInstance()->getVariable(Variable::GAMIFICATION_DELAY);

            if (!Statistics_Service_Manager::checkIfAllowed(Statistics::CHANNEL_STAND_PRODUCT_APPLY, $this->getSelectedEvent()->getId(), $delay)) {
                if ($this->userAuth) {
                    $this->userAuth->setIsBot(1);
                    $this->userAuth->save();
                }
            } else {
                Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_PRODUCT_APPLY, $this->_exhibStand->getId(), $this->getSelectedEvent()->getId(), [$this->_standProduct->getId()], $this->_standProduct->getId());
            }

            // przekierowanie na strone
            $this->_helper->redirector->gotoUrl($this->_standProduct->getFormTarget());
        }
    }

    public function detailsAction()
    {
        $this->_standProduct = StandProduct::findOneByHash($this->_getParam('product_hash'));
        $this->forward404Unless($this->_standProduct, 'StandProduct not found, hash: (' . $this->_getParam('product_hash') . ')');

        // Statystyki
        Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_PRODUCT_VIEW, $this->_exhibStand->getId(), $this->getSelectedEvent()->getId(), [$this->_standProduct->getId()], $this->_standProduct->getId());

        $this->view->standProduct = $this->_standProduct;
        $this->addProductContactForm();

        $this->view->id_briefcase_type = BriefcaseType::TYPE_BRIEFCASE_PRODUCT;

        //$currency = new Zend_Currency();
        $this->view->currency = ' zł';

        $this->view->headMeta()->setName('keywords', $this->_standProduct->getKeywords());
        $this->view->headMeta()->setName('description', $this->_standProduct->getLead());
        $this->view->headTitle()->prepend($this->_standProduct->getName());
        //tagi dla FB
        $this->view->headMeta()->setName('og:title', $this->_standProduct->getName());
        $this->view->headMeta()->setName('og:description', $this->_exhibStand->getName() . ' - ' . $this->_standProduct->getLead());
        $this->view->headMeta()->setName('og:site_name', $this->_exhibStand->Event->getTitle());

        $imageid = $this->_standProduct->id_fb_image !== 0 ? $this->_standProduct->id_fb_image : $this->_standProduct->id_image;
        $this->view->headMeta()->setName('og:image', Service_Image::getUrl($imageid, 158, 158, 'bi'));

        if ($this->getSelectedEvent()->fb_app_id !== []) {
            $this->view->headMeta()->setName('fb:app_id', $this->getSelectedEvent()->fb_app_id);
        }

        $this->view->backTo = $this->_getParam('back_to');

        $this->_breadcrumb[] = [
            'url' => $this->view->url(),
            'label' => $this->view->escape($this->_standProduct->getName()),
        ];
    }

    public function shopAction()
    {
        $this->_standProduct = StandProduct::findOneByHash($this->_getParam('product_hash'));
        $this->forward404Unless($this->_standProduct, 'StandProduct not found, hash: (' . $this->_getParam('product_hash') . ')');

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->_standProduct->isLink()) {
            // Statystyki
            Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_SHOP_VIEW, $this->_exhibStand->getId(), $this->getSelectedEvent()->getId(), [$this->_standProduct->getId()]);
            // przekierowanie na stronę facebook
            $this->_helper->redirector->gotoUrl($this->_standProduct->getLink());
        }
        exit();
    }

    public function downloadAction()
    {
        $file = StandProductFile::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($file, 'File NOT Exists (' . $this->_getParam('hash') . ')');

        $filename = $file->getName();
        $file_path = $file->getRelativeFile();

        if (!empty($file_path) && file_exists($file_path)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($file_path);
        } else {
            echo 'The specified file does not exist : ' . $file_path;
        }
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    private function addContactForm($is_chat = 0)
    {
        $formContact = new Event_Form_Stand_Inquiry_Contact([
            'baseUser' => $this->getSelectedBaseUser(),
            'user' => $this->userAuth,
            'exhibStand' => $this->_exhibStand,
        ]);
        $is_chat && $formContact->setAttrib('id', $formContact->getElementsBelongTo() . 'Form_Chat');
        $is_chat && $formContact->setAction($this->view->url(['event_uri' => $this->getSelectedEvent()->getUri(),
            'stand_uri' => $this->_exhibStand->getUri(),
            'hall_uri' => $this->_exhibStand->EventStandNumber->EventHallMap->uri,
        ], 'event_stand_contact_chat'));
        $this->view->formContact = $formContact;
    }

    private function addProductContactForm()
    {
        $formProductContact = new Event_Form_StandProduct_Contact([
            'baseUser' => $this->getSelectedBaseUser(),
            'user' => $this->userAuth,
            'standProduct' => $this->_standProduct,
        ]);

        if ($this->getRequest()->isPost()) {
            if ($formProductContact->isValid($this->getRequest()->getPost())) {
                $delay = Engine_Variable::getInstance()->getVariable(Variable::GAMIFICATION_DELAY);
                if (!Statistics_Service_Manager::checkIfAllowed(Statistics::CHANNEL_STAND_PRODUCT_APPLY, $this->getSelectedEvent()->getId(), $delay)) {
                    if ($this->displayJsonResponse) {
                        $this->jsonResult['messageType'] = 'html';
                        $this->jsonResult['message'] = '<div class="message"><div class="message-warning">' .
                            $this->view->translate('message_stand-inquery-contact_delay') .
                            '</div></div>';
                        $this->jsonResult['content_id'] = $formProductContact->getElementsBelongTo() . 'Form';
                    } else {
                        $formProductContact = $this->view->translate('message_stand-inquery-contact_delay');
                    }
                } else {
                    Statistics_Service_Manager::add(Statistics::CHANNEL_STAND_PRODUCT_APPLY, $this->_exhibStand->getId(), $this->getSelectedEvent()->getId(), [$this->_standProduct->getId()], $this->_standProduct->getId());

                    $url = $this->view->url(
                        ['event_uri' => $this->getSelectedEvent()->getUri(), 'stand_uri' => $this->_exhibStand->getUri(), 'product_hash' => $this->_standProduct->getHash()],
                        'event_stand-offer_details'
                    );

                    if ($this->displayJsonResponse) {
                        $this->jsonResult['messageType'] = 'html';
                        $this->jsonResult['message'] = '<div class="message"><div class="message-success">' .
                            $this->view->translate('message_stand-product_contact_send') .
                            '</div></div>';
                        $this->jsonResult['content_id'] = $formProductContact->getElementsBelongTo() . 'Form';
                    } else {
                        $this->_redirector->gotoUrlAndExit($url);
                    }
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['messageType'] = 'html';
                $this->jsonResult['message'] = $formProductContact->render();
                $this->jsonResult['content_id'] = $formProductContact->getElementsBelongTo() . 'Form';
            }
        }

        $this->view->formProductContact = $formProductContact;
    }
}
