<?php

class Event_AdminStandProductController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_Admin_StandProduct_Filter|mixed
     */
    public $filter;
    /**
     * @var \Event_Form_Admin_StandProduct_Edit|\Event_Form_Admin_StandProduct_New|mixed
     */
    public $_standProductForm;
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

        $this->_exhibStand = ExhibStand::findOneByHashAndBaseUser($this->_getParam('stand_hash'), $this->getSelectedBaseUser());
        $this->checkExhibStandAccess();

        $this->view->exhibStand = $this->_exhibStand;
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin-stand/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        //Deklaracja tablicy z filtrami
        $search = ['product_name' => '', 'is_active' => ''];

        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('product_filter');
        } elseif (isset($search)) {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->product_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->product_filter->{$key}) ? $this->_session->product_filter->{$key} : $value);
            }
        }

        $this->filter = new Event_Form_Admin_StandProduct_Filter();
        $this->filter->populate($search);

        $standProductQuery = Doctrine_Query::create()
            ->from('StandProduct sp')
            ->leftJoin('sp.Translations t')
            ->innerJoin('sp.ExhibStand es')
            ->addWhere('sp.id_exhib_stand = ?', $this->_exhibStand->getId())
            ->orderBy('t.name ASC')
        ;

        //sprawdzenie nazwy z filtra
        if (!empty($search['product_name'])) {
            $standProductQuery->addWhere('t.name LIKE ?', '%' . $search['product_name'] . '%');
        }

        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $standProductQuery->addWhere('sp.is_active = ?', $search['is_active']);
        }

        $pager = new Doctrine_Pager($standProductQuery, $this->_getParam('page', 1), 20);
        $standProductList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->standProductList = $standProductList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_offer-list'));
        $this->view->filter = $this->filter;
    }

    public function editAction()
    {
        $this->_standProduct = StandProduct::findOneByHash($this->_getParam('product_hash'));
//                var_dump($this->_standProduct->StandProductFile);exit;
        $this->forward403Unless($this->_standProduct, 'StandProduct not found hash: (' . $this->_getParam('product_hash') . ')');

        $this->_standProductForm = new Event_Form_Admin_StandProduct_Edit($this->_standProduct);

        $this->productForm();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_offer-edit'));
    }

    public function newAction()
    {
        $this->_standProduct = new StandProduct();
        $this->_standProduct->ExhibStand = $this->_exhibStand;
        $this->_standProduct->hash = $this->engineUtils->getHash();

        $this->_standProductForm = new Event_Form_Admin_StandProduct_New($this->_standProduct);
        $this->productForm();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_offer-new'));
    }

    public function deleteAction()
    {
        $this->_standProduct = StandProduct::findOneByHash($this->_getParam('product_hash'));
        $this->forward403Unless($this->_standProduct, 'StandProduct not found hash: (' . $this->_getParam('product_hash') . ')');

        $this->_exhibStand = $this->_standProduct->ExhibStand;
        $this->_standProduct->delete();

        //zerujemy liczniki produktów i promocji
        $this->_standProduct->ExhibStand->count_products = null;
        $this->_standProduct->ExhibStand->count_bargains = null;
        $this->_standProduct->ExhibStand->save();

        $this->_flash->succes->addMessage($this->view->translate('message_delete_save'));
        $this->_redirector->gotoRouteAndExit(
            ['stand_hash' => $this->_exhibStand->getHash()],
            'event_admin-stand-offer_index'
        );
    }

    public function statusAction()
    {
        $product_hash = $this->_getParam('product_hash');

        if (!$product_hash) {
            $this->_flash->error->addMessage('StandProduct not found');
            $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand-offer_index');
        }
        $standProduct = StandProduct::findOneByHash($product_hash);

        $this->forward404If(false === $standProduct, 'StandProduct not exists,hash(' . $product_hash . ')');

        $standProduct->is_active = (!(bool) $standProduct->getIsActive());
        $standProduct->save();

        //zerujemy liczniki produktów i promocji
        $standProduct->ExhibStand->count_products = null;
        $standProduct->ExhibStand->count_bargains = null;
        $standProduct->ExhibStand->save();

        $this->_flash->succes->addMessage('Status changed');
        $this->_redirector->gotoRouteAndExit(['stand_hash' => $this->_exhibStand->getHash()], 'event_admin-stand-offer_index');
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

    private function productForm()
    {
        if ($this->_request->isPost() && $this->_standProductForm->isValid($this->_request->getPost())) {
            $data = $this->_request->getPost();
            $this->_standProduct->setName($data['name']);
            $this->_standProduct->setLead($data['lead']);
            $this->_standProduct->setKeywords($data['keywords']);
            if ($this->_standProduct->ExhibStand->Event->getIsShopActive()) {
                $this->_standProduct->setLink($data['link']);
            }
            $this->_standProduct->setDescription($data['description']);
            $this->_standProduct->setIsActive($data['is_active']);
            $this->_standProduct->setIsPromotion($data['is_promotion']);
            $this->_standProduct->setPrice($data['price']);
            $this->_standProduct->setOriginalPrice($data['original_price']);
            $this->_standProduct->setUnit($data['unit']);
            $this->_standProduct->setFormTarget($data['form_target']);
            $this->_standProduct->setOfferCity($data['offer_city']);
            $this->_standProduct->setSkipOfferPage($data['skip_offer_page']);
            $this->_standProduct->save();
            //zerujemy licznik produktów
            $this->_standProduct->ExhibStand->count_products = null;
            $this->_standProduct->ExhibStand->count_bargains = null;
            $this->_standProduct->ExhibStand->save();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $fileInfo = $upload->getFileInfo();
            if (isset($fileInfo['image']) && 0 === $fileInfo['image']['error']) {
                $image = Service_Image::createImage(
                    $this->_standProduct,
                    [
                        'type' => $fileInfo['image']['type'],
                        'name' => $fileInfo['image']['name'],
                        'source' => $fileInfo['image']['tmp_name'], ]
                );

                $this->_standProduct->id_image = $image->getId();
                $this->_standProduct->save();
            }
            if (isset($fileInfo['fb_image']) && 0 === $fileInfo['fb_image']['error']) {
                $image = Service_Image::createImage(
                    $this->_standProduct,
                    [
                        'type' => $fileInfo['fb_image']['type'],
                        'name' => $fileInfo['fb_image']['name'],
                        'source' => $fileInfo['fb_image']['tmp_name'], ]
                );

                $this->_standProduct->id_fb_image = $image->getId();
                $this->_standProduct->save();
            }
            if (isset($fileInfo['file']) && 0 === $fileInfo['file']['error']) {
                if (!$this->_standProduct->hasFile()) {
                    $standProductFile = new StandProductFile();
                    $standProductFile->setIdStandProduct($this->_standProduct->getId());
                    $standProductFile->setHash(Engine_Utils::getInstance()->getHash());
                } else {
                    $standProductFile = StandProductFile::findOneByIdStandProduct($this->_standProduct->getId());
                    $standProductFile->deleteFile();
                }

                $standProductFile->setTitle($data['file_name']);
                $standProductFile->setName($fileInfo['file']['name']);

                $tmp_name = $fileInfo['file']['tmp_name'];
                $file_ext = $this->engineUtils->getFileExt($fileInfo['file']['name']);
                $standProductFile->setFileExt($file_ext);
                $standProductFile->save();
                move_uploaded_file($tmp_name, $standProductFile->getRelativeFile());
            }
            $this->_flash->succes->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit(
                ['stand_hash' => $this->_standProduct->ExhibStand->getHash(), 'product_hash' => $this->_standProduct->getHash()],
                'admin_stand-offer_edit'
            );
        }

        $this->_helper->viewRenderer('form');
        $this->view->standProductForm = $this->_standProductForm;
    }

    private function checkExhibStandAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->_exhibStand, 'ExhibStand NOT Exists (' . $this->_getParam('stand_hash') . ')');

        $this->forward403Unless(
            $this->userAuth->hasAccess(null, $this->_exhibStand)
        );
    }
}
