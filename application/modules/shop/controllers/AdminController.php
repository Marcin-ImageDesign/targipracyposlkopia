<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 21.10.13
 * Time: 13:01
 * To change this template use File | Settings | File Templates.
 */
class Shop_AdminController extends Engine_Controller_Admin
{
    /**
     * @var Shop_Form_Filter
     */
    protected $_filter;

    /**
     * @var ShopOrder
     */
    private $_shopOrder;

    /**
     * @var ExhibStand
     */
    private $_exhibStand;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->_filter = new Shop_Form_Filter();

        $pager = new Doctrine_Pager($this->_filterList(), $this->_getParam('page', 1), 50);
        $shopOrderList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->shopOrderList = $shopOrderList;
        $this->view->filter = $this->_filter;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_h1_admin_shop-index'));
    }

    public function showAction()
    {
        $this->_shopOrder = Doctrine_Query::create()
            ->from('ShopOrder so')
            ->leftJoin('so.User u')
            ->innerJoin('so.ExhibStand es')
            ->innerJoin('so.Products sop')
            ->innerJoin('sop.Product p')
            ->limit(1)
            ->where('so.hash = ?', $this->getParam('hash'))
            ->fetchOne()
        ;

        $this->_shopOrder->isRead();

        $this->forward403Unless($this->_shopOrder->ExhibStand->hasAccess($this->userAuth), [$this->getSelectedBaseUser()->getId(), $this->_shopOrder->getId()]);

        $this->view->shopOrder = $this->_shopOrder;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_admin_shop_order-nonr') . ' #' . $this->_shopOrder->id_shop_order);
    }

    public function deleteAction()
    {
        $this->_shopOrder = ShopOrder::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->_shopOrder, 'Order Not Found hash: (' . $this->_getParam('hash') . ')');
        $this->forward403Unless($this->_shopOrder->ExhibStand->hasAccess($this->userAuth), [$this->getSelectedBaseUser()->getId(), $this->_shopOrder->getId()]);

        $this->_shopOrder->delete();

        $this->_flash->succes->addMessage('Order has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'shop_admin');
    }

    public function exportListAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $shopOrderList = $this->_filterList()->execute();

        $fileName = date('Y-m-d_H:i:s');

        $this->_response->setHeader('Content-Type', 'application/csv')->setHeader('Content-Disposition', 'attachment;charset=utf-8; filename=' . $fileName . '.csv');

        $fp = fopen('php://output', 'w');

        foreach ($shopOrderList as $value) /* @var $value ShopOrder */ {
            $shopOrderArray = [];
            $shopOrderArray['id_shop_order'] = $value->getIdShopOrder();
            $shopOrderArray['created_at'] = $value->getCreatedAt();
            $shopOrderArray['user_order_name'] = $value->User->getName();
            $shopOrderArray['exhib_stand_name'] = $value->ExhibStand->getName();
            $shopOrderArray['price'] = $value->getPrice();
            $prodLine = [];
            foreach ($value->Products as $shopOrderProduct) /* @var $shopOrderProduct ShopOrderProduct */ {
                $prods = [];
                $prods['stand_product_name'] = $shopOrderProduct->Product->getName();
                $prods['count'] = $shopOrderProduct->getCount();
                $prods['price'] = $shopOrderProduct->getPrice();
                $prods['price_total'] = $shopOrderProduct->getPriceTotal();

                $prodLine[] = implode('-', $prods);
            }
            $shopOrderArray['product'] = implode("\r\n", $prodLine);

            fputcsv($fp, $shopOrderArray, ';');
        }
        fclose($fp);
    }

    private function _filterList()
    {
        $exhibStandArray = null;
        $countStand = null;
        $search = ['date_start' => '', 'date_end' => '', 'price_start' => '', 'price_end' => '', 'is_read' => ''];

        if ($this->_hasParam('clear')) {
            $this->_session->__unset('shop_order_list_filter');
        } else {
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->shop_order_list_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->shop_order_list_filter->{$key}) ? $this->_session->shop_order_list_filter->{$key} : $value);
            }
        }

        if (isset($this->_filter)) {
            $this->_filter->populate($search);
        }

        $shopOrderQuery = Doctrine_Query::create()
            ->from('ShopOrder so')
            ->innerJoin('so.User u')
            ->innerJoin('so.ExhibStand es')
            ->innerJoin('so.Products sop')
            ->innerJoin('sop.Product p')
        ;

        if ($this->userAuth->isExhibotor()) {
            $this->_exhibStand = ExhibStand::findStandsByAuthUser($this->getSelectedBaseUser(), $this->userAuth, $this->getSelectedEvent()->getId());
            foreach ($this->_exhibStand as $value) { // @var $value ExhibStand
                $exhibStandArray[] = $value->getId();
            }
            $countStand = count($exhibStandArray);
            $shopOrderQuery->whereIn('so.id_exhib_stand', $exhibStandArray);
        } elseif (!$this->userAuth->isAdmin() && !$this->userAuth->isOrganizer()) {
            $shopOrderQuery->where('so.id_user = ?', $this->userAuth->getId());
        }

        if ($this->hasSelectedEvent()) {
            $shopOrderQuery->addWhere('es.id_event = ?', $this->getSelectedEvent()->getId());
        }

        // początek filtra
        if (!empty($search['date_start'])) {
            $shopOrderQuery->addWhere('so.created_at >= ?', $search['date_start'] . ' 00:00:00');
        }

        if (!empty($search['date_end'])) {
            $shopOrderQuery->addWhere('so.created_at <= ?', $search['date_end'] . ' 23:59:59');
        }

        if (!empty($search['price_start'])) {
            $shopOrderQuery->addWhere('so.price >= ?', $search['price_start']);
        }

        if (!empty($search['price_end'])) {
            $shopOrderQuery->addWhere('so.price <= ?', $search['price_end']);
        }

        if (mb_strlen($search['is_read']) > 0) {
            $shopOrderQuery->addWhere('so.is_read = ?', $search['is_read']);
        }

        $shopOrderQuery->orderBy('so.is_read ASC, so.created_at DESC');

        if ($this->userAuth->isAdmin() || ($this->userAuth->isExhibotor() && $countStand > 1)) {
            $this->view->checkToShowStandName = true;
        } else {
            $this->view->checkToShowStandName = false;
        }

        return $shopOrderQuery;
    }
}
