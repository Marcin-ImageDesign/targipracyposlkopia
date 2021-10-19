<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 01.10.13
 * Time: 15:03
 * To change this template use File | Settings | File Templates.
 */
class Briefcase_ShopController extends Engine_Controller_Frontend
{
    /**
     * @var Briefcase_Service_Model
     */
    private $_briefcaseService;

    private $_briefcaseList;

    private $_briefcaseIdCount;

    /**
     * @var Doctrine_Collection
     */
    private $_shopOrderList;

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
    }

    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->_helper->viewRenderer->getNoRender() && $this->_helper->layout->isEnabled()) {
            if ($this->hasSelectedEvent()) {
                $this->view->renderToPlaceholder('user/_section_nav.phtml', 'section-nav-content');
            }
        }
    }

    public function indexAction()
    {
        $this->_getBriefcaseList();

        $shopLocationArray = [];
        $shopLocationList = Doctrine_Query::create()
            ->from('ShopLocation sl INDEXBY sl.id_shop_location')
            ->where('sl.id_event = ?', $this->getSelectedEvent()->getId())
            ->execute()
        ;

        /** @var ShopLocation $shopLocation */
        foreach ($shopLocationList as $shopLocation) {
            $shopLocationArray[$shopLocation->getId()] = $shopLocation->getName();
        }

        $this->view->choosenLocation = $this->_getShopLocationFromCookie();
        $this->view->shopLocationOptions = $shopLocationArray;
    }

    public function addAction()
    {
        $params = $this->getAllParams();

        if (is_numeric($params['value'])) {
            if ($params['value'] > 0) {
                $brefcase = $this->_briefcaseService->addElement(
                    BriefcaseType::TYPE_BRIEFCASE_SHOP,
                    $params['element'],
                    $this->getSelectedEvent()->getId(),
                    $params['value']
                );
            } else {
                $brefcase = $this->_briefcaseService->removeElement(
                    BriefcaseType::TYPE_BRIEFCASE_SHOP,
                    $params['element'],
                    $this->getSelectedEvent()->getId()
                );
            }
        } else {
            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'shop_index');
        }

        // odpowiedx dla flash
        if ($this->hasParam('isFlashHttpRequest')) {
            echo '&ret=true';
            exit();
        }

        if (!$this->_request->isXmlHttpRequest()) {
            // $this->_flash->success->addMessage('Save successfully completed');
            if ($this->hasParam('return')) {
                $return = $this->_getParam('return');
                $this->_redirector->gotoUrlAndExit($return);
            }

            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'shop_index');
        } else {
            $this->jsonResult['result'] = true;
            $this->jsonResult['link'] = $this->view->url(
                ['element' => $params['element'],
                    'id_briefcase_type' => BriefcaseType::TYPE_BRIEFCASE_SHOP,
                    'namespace' => $this->getSelectedEvent()->getId(),
                ],
                'briefcase_remove-element'
            );
            $this->jsonResult['title'] = $this->view->translate('label_briefcase_remove-from-briefcase');
        }
    }

    public function removeAction()
    {
        $element = $this->_getParam('element');
        $this->forward403Unless($element, 'Element is required');

        $brefcase = $this->_briefcaseService->removeElement(
            BriefcaseType::TYPE_BRIEFCASE_SHOP,
            $element,
            $this->getSelectedEvent()->getId()
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
                'id_briefcase_type' => BriefcaseType::TYPE_BRIEFCASE_SHOP,
                'element' => $element,
                'namespace' => $this->getSelectedEvent()->getId(),
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

            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'shop_index');
        }
    }

    public function orderSummaryAction()
    {
        $choosenLocationArr = null;
        $this->_getBriefcaseList();

        $clientData = User::findOneByHash($this->userAuth->getHash());

        $choosenLocationName = '';
        if ($this->_request->isPost()) {
            $choosenLocation = ShopLocation::find($this->getParam('id_shop_location'));
            $choosenLocationArr['id_shop_location'] = $choosenLocation->getId();
            setcookie('choosenShopLocation', serialize($choosenLocationArr), time() + 7200, '/');
            $choosenLocationName = $choosenLocation->getName();
        } else {
            $choosenLocation = $this->_getShopLocationFromCookie();
            $choosenLocationName = $choosenLocation->getName();
        }

        $this->view->choosenLocationName = $choosenLocationName;
        $this->view->clientData = $clientData;
    }

    public function sendOrderAction()
    {
        $this->_getBriefcaseList();

        // set client data to see in email
        $clientData = User::findOneByHash($this->userAuth->getHash());
        $this->view->clientData = $clientData;

        $save = $this->_saveOrderToBase();

        if ($save) {
            $this->_sendOrderToClientAndLocation();
            if ($this->getSelectedEvent()->isConfirmSendEmailToExhibitor()) {
                $this->_sendOrderToParticipation();
            } else {
                $this->_sendEmailForOrder();
            }

            foreach ($this->_briefcaseList as $key => $item) {
                $brefcase = $this->_briefcaseService->removeElement(
                    BriefcaseType::TYPE_BRIEFCASE_SHOP,
                    $item['id_stand_product'],
                    $this->getSelectedEvent()->getId()
                );
            }

            if (isset($_COOKIE['choosenShopLocation'])) {
                setcookie('choosenShopLocation', '', time() + 7200, '/');
            }

            $this->_flash->success->addMessage($this->view->translate('label_shop_send-order_ok'));
            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'shop_thx-send-order');
        } else {
            $this->_flash->error->addMessage($this->view->translate('label_shop_send-order_error'));
            $this->_redirector->gotoRouteAndExit(['event_uri' => $this->getSelectedEvent()->getUri()], 'shop_order-summary');
        }
    }

    public function thxSendOrderAction()
    {
    }

    /**
     * Get all product from session, database or cookie you added to briefcase.
     */
    private function _getBriefcaseList()
    {
        $id_namespace = null;
        if ($this->hasSelectedEvent()) {
            $id_namespace = $this->getSelectedEvent()->getId();
        }

        $briefcaseType = BriefcaseType::find(BriefcaseType::TYPE_BRIEFCASE_SHOP);
        $this->_briefcaseIdCount = $this->_briefcaseService->getElementsByType($briefcaseType, $id_namespace);

        if (!empty($this->_briefcaseIdCount)) {
            $this->_briefcaseList = Doctrine_Query::create()
                ->from('StandProduct esf')
                ->whereIn('esf.id_stand_product', ($this->_briefcaseIdCount))
                ->execute()
            ;

            $this->view->briefcaseIdCount = $this->_briefcaseIdCount;
            $this->view->briefcaseList = $this->_briefcaseList;
        }

    }

    private function _getBriefcaseListForParticipation()
    {
    }

    private function _sendOrderToClientAndLocation()
    {
        // setting data for addTo
        $emailClient = $this->userAuth->getEmail();
        $clientName = $this->userAuth->getFirstName() . ' ' . $this->userAuth->getLastName();
        $this->view->shopOrderList = $this->_shopOrderList;
        // settings for email & send
        $smtpOptions = $this->getSelectedBaseUser()->getSettings('smtp');
        $mail = new Engine_Mail($smtpOptions);
        $mail->setBodyHtml($this->view->render('_mail_order_layout.phtml'));
        $mail->setSubject($this->view->translate('label_shop_send-order_email-subject'));
        $mail->addTo($emailClient, $clientName);

        $send = $mail->send();

        foreach ($this->_shopOrderList as $shopOrder) { /** @var ShopOrder $shopOrder */
            if (isset($shopOrder->ShopLocation)) {
                $mail = new Engine_Mail($smtpOptions);
                $mail->clearRecipients();
                $mail->setBodyHtml($this->view->render('_mail_order_layout.phtml'));
                $mail->setSubject($this->view->translate('label_shop_send-order_email-subject'));

                $mail->addTo($shopOrder->ShopLocation->getEmail());
                $send = $mail->send();
            } else {
                $send = false;
            }
        }

        return $send;
    }

    private function _sendEmailForOrder()
    {
        $this->view->shopOrderList = $this->_shopOrderList;
        // settings for email & send
        $smtpOptions = $this->getSelectedBaseUser()->getSettings('smtp');
        $mail = new Engine_Mail($smtpOptions);
        $mail->setBodyHtml($this->view->render('_mail_order_layout.phtml'));
        $mail->setSubject($this->view->translate('label_shop_send-order_email-subject'));
        $mail->addTo($this->getSelectedEvent()->getEmailForOrder());

        return $mail->send();
    }

    private function _sendOrderToParticipation()
    {
        $participationToProduct = [];

        // settings for email & send
        $smtpOptions = $this->getSelectedBaseUser()->getSettings('smtp');
        foreach ($this->_shopOrderList as $value) { /** @var ShopOrder $value */
            foreach ($value->ExhibStand->ExhibStandParticipation as $value2) { /** @var ExhibStandParticipation $value2 */
                if (!$value2->is_active) {
                    continue;
                }
                $participationToProduct[$value2->ExhibParticipation->User->id_user][] = $value;
            }
        }

        $send = false;
        foreach ($participationToProduct as $id_user => $participation) {
            $user = User::find($id_user);

            $this->view->shopOrderList = $participation;

            $mail = new Engine_Mail($smtpOptions);
            $mail->clearRecipients();
            $mail->setBodyHtml($this->view->render('_mail_order_layout.phtml'));
            $mail->setSubject($this->view->translate('label_shop_send-order_email-subject'));

            $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
            $send = $mail->send();
        }

        return $send;
    }

    /**
     * Get the shop location from cookie if isset.
     *
     * @return false|ShopLocation
     */
    private function _getShopLocationFromCookie()
    {
        if (isset($_COOKIE['choosenShopLocation'])) {
            $array = unserialize($_COOKIE['choosenShopLocation']);

            return ShopLocation::find($array['id_shop_location']);
        }

        return false;
    }

    /**
     * This private method save order to database.
     *
     * @return bool
     */
    private function _saveOrderToBase()
    {
        $inquiryByStand = [];
        foreach ($this->_briefcaseList as $product) {
            $inquiryByStand[$product->id_exhib_stand][] = $product;
        }

        $this->_shopOrderList = new Doctrine_Collection('ShopOrder');

        foreach ($inquiryByStand as $id_exhib_stand => $products) {
            $shopOrder = new ShopOrder();
            $shopOrder->id_exhib_stand = $id_exhib_stand;
            if (false !== $this->_getShopLocationFromCookie()) {
                $shopOrder->ShopLocation = $this->_getShopLocationFromCookie();
            }
            $shopOrder->hash = Engine_Utils::_()->getHash();
            $shopOrder->User = $this->userAuth;

            foreach ($products as $product) {
                $shopOrderProduct = new ShopOrderProduct();
                $shopOrderProduct->Product = $product;
                $shopOrderProduct->count = $this->_briefcaseIdCount[$product->getId()];
                $shopOrderProduct->price = $product->price;
                $shopOrderProduct->price_total = $shopOrderProduct->price * $shopOrderProduct->count;

                $shopOrder->Products->add($shopOrderProduct);
                $shopOrder->price += $shopOrderProduct->price_total;
            }

            $this->_shopOrderList->add($shopOrder);
        }

        try {
            $this->_shopOrderList->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
