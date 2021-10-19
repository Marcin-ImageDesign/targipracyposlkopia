<?php

class Inquiry_AdminController extends Engine_Controller_Admin
{
    public function indexAction()
    {
        $searchParams = null;
        $baseUrl = $this->view->url([], 'inquiry_admin', true);

        // strona, ilość na stronie
        $page = (int) $this->getRequest()->getQuery('page', 1);

        // ---------
        // ----- Filtr
        // ---------

        // $filter = new Inquiry_Form_Filter_Item(array(
        //     'baseUser' => $this->getSelectedBaseUser()
        // ));

        // $searchParams = $this->_helper->queryParams(
        //     $filter->getParamKeys(),
        //     $baseUrl,
        //     false
        // );

        // if($filter->isValid($searchParams)) {
        //     $searchParams = $filter->getValues();
        // } else {
        //     $searchParams = array();
        // }

        // ---------
        // ----- End Filtr
        // ---------

        // widzimy zagłoszenia dedykowane tylko dla danej aplikacji
        $searchParams['idBaseUser'] = $this->getSelectedBaseUser()->getId();

        if ($this->_getParam('inquiry_channel')) {
            $searchParams['channel'] = $this->_getParam('inquiry_channel');
        }

//        $this->setSelectedEvent($this->_getParam('setSelectedEvent'));
        //pokazujemy tylko te zgloszenia, do ktorych ma prawo zalogowany user
        $exhibitorStandsArray = ['0'];

        if ($this->hasSelectedEvent()) {
            $exhib_stands = ExhibStand::findStandsByAuthUser($this->getSelectedBaseUser(), $this->userAuth, $this->getSelectedEvent()->getId());
            foreach ($exhib_stands as $stand) {
                $exhibitorStandsArray[] = $stand->getId();
            }
        }

        if (!empty($exhibitorStandsArray)) {
            $searchParams['standsArray'] = $exhibitorStandsArray;
        }

        // opcje
        $options = [
            'paginator' => false,
            'search' => $searchParams,
        ];

        // pobranie ofert
        $query = Inquiry_Service_Search::getByOptions($options);
        //dociągnięcie emaila interesanta
        $clientsList = $query->execute();

        $clientJson = [];
        foreach ($clientsList as $client) {
            $clientJson[$client->getHash()] = (array) Zend_Json::decode($client->data);
        }

        $pager = new Doctrine_Pager($query, $page, 20);
        $list = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->clientArray = $clientJson;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->list = $list;

        // $this->view->filter = $filter;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Inquiries'));
        $this->view->renderToPlaceholder('admin/_contentNavItem.phtml', 'nav-left');
    }

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $item = Doctrine_Core::getTable('Inquiry')->find($id);
        $this->forward404Unless($item, 'Inquiry Not Found! id: (' . $id . ')');
        $this->view->item = $item;

        $baseData = [
            $this->view->translate('Channel') => $item->channel,
            $this->view->translate('Created') => $item->created_at,
        ];

        $json = [];

        try {
            $json = (array) Zend_Json::decode($item->data);
            $json = $baseData + $json;
            if (StandProduct::INQUIRY_CHANNEL_PRODUCT === $json['Channel']) {
                $product = StandProduct::find($json['id_product']);

                if ($product) {
                    $this->view->product = $product;
                    $json = array_merge(['product' => $product->getName()], $json);
                    unset($json['id_product'], $json['Channel']);
                }
            } elseif (ExhibStand::INQUIRY_CHANNEL === $json['Channel']) {
                $stand = null;
                if (isset($json['id_stand'])) {
                    $stand = ExhibStand::find($json['id_stand']);
                }

                if ($stand) {
                    $this->view->stand = $stand;
                    $json = array_merge(['stand' => $stand->getName()], $json);
                    unset($json['id_stand'], $json['Channel']);
                }
            }
        } catch (Exception $e) {
        }
        $this->view->items = $json;

        $this->view->tlabel = 'form_inquiry_view_';
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Inquiries'));
        $this->view->renderToPlaceholder('admin/_contentNavItem.phtml', 'nav-left');
    }

    public function deleteAction()
    {
        $item = Doctrine_Core::getTable('Inquiry')->find((int) $this->getRequest()->getParam('id'));
        $this->forward404Unless($item, 'Inquiry Not Found! id: (' . (int) $this->getRequest()->getParam('id') . ')');
        $this->forward404Unless(
            $this->getSelectedBaseUser()->hasAccess($item),
            'Lack of privillages'
        );

        Inquiry_Service_Manager::delete($item);

        $this->_flash->success->addMessage($this->view->translate('Inquiry have been deleted'));

        $this->_redirector->gotoRouteAndExit([], 'inquiry_admin');
    }

    public function getAttachmentAction()
    {
        $item = Doctrine_Core::getTable('Inquiry')->find((int) $this->getRequest()->getParam('id'));
        $this->forward404Unless($item, 'Inquiry Not Found! id: (' . (int) $this->getRequest()->getParam('id') . ')');
        $this->forward404Unless(
            $this->getSelectedBaseUser()->hasAccess($item),
            'Lack of privillages'
        );

        if ($item->hasFile()) {
            $file = InquiryFile::findOneByIdInquiry($item->getId());
            $this->forward404Unless($file, 'File NOT Exists (' . $this->_getParam('hash') . ')');

            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $filename = $file->getName();
            $file_path = $file->getRelativeFile();

            if (!empty($file_path) && file_exists($file_path)) {
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                readfile($file_path);
            } else {
                echo 'The specified file does not exist : ' . $file_path;
            }
        }
    }

    // public function inquiriesAction(){

    //     $inquiriesQuery = Doctrine_Query::create()
    //         ->from('inquiry i')
    //         ->where('i.id_base_user = ?', $this->getSelectedBaseUser->getId())
    //         ->execute();

    //     $this->view->inquiries = $inquiriesQuery

    // }
}
