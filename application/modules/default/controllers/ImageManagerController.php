<?php

class ImageManagerController extends Engine_Controller_Admin
{
    private $maxImagesLimit = 100;

    /**
     * Strona html.
     */
    public function indexAction()
    {
        $item = $this->getItem();
        $id = $item->getId();
        if (method_exists($item, 'getHash')) {
            $id = $item->getHash();
        }

        $this->view->item = $item;
        $this->view->id = $id;
        $this->view->mode = $mode = $this->getRequest()->getParam('mode');
        // inline - bez użycia iframe
        if ('inline' !== $mode) {
            $this->_helper->layout()->setLayout('layout-image-manager');
        }
        $limit = (int) $this->getRequest()->getParam('limit');
        if (0 === $limit || $limit > $this->maxImagesLimit) {
            $limit = $this->maxImagesLimit;
        }
        $this->view->limit = $limit;
    }

    /**
     * Lista wszystkich zdjęć danego elementu.
     */
    public function listAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $item = $this->getItem();
        $images = [];

        $all = Service_Image::getAll(
            $item,
            ['hydrate' => Doctrine_Core::HYDRATE_ARRAY, 'field' => 'id_image']
        );
        foreach ($all as $img) {
            $images[] = [
                'src' => Service_Image::getUrl($img['id_image'], 145, 125, 'a'),
                'zoom' => Service_Image::getUrl($img['id_image'], 900, 700, 'b'),
                'id' => $img['id_image'],
            ];
        }

        $this->_helper->json($images);
    }

    public function uploadAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $item = $this->getItem();

        if (Service_Image::getAll($item)->count() > $this->maxImagesLimit) {
            echo '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream (max images 50)."}, "id" : "id"}';
            die;
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        try {
            $filePath = $this->_helper->upload(APPLICATION_TMP . '/upload');

            Service_Image::createImage(
                $item,
                [
                    'type' => $_FILES['file']['type'],
                    'name' => $_FILES['file']['name'],
                    'source' => $filePath, ]
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function orderAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $item = $this->getItem();

        $order = $this->getRequest()->getPost('order');

        $all = Service_Image::getAll($item);
        foreach ($all as $img) {
            $imgOrder = array_search($img->getId(), $order, true);
            if (false !== $imgOrder) {
                $img->order = $imgOrder;
                Service_Image::saveImage($img);
            }
        }
    }

    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $item = $this->getItem();

        $id = $this->getRequest()->getPost('id');

        $image = Service_Image::getImage($id);
        if (!$image || $image->object_id !== $item->getId() || $image->class_name !== get_class($item)) {
            $this->getResponse()->setBody('Error! Image not found.')->sendResponse();
            exit;
        }
        if ($this->getRequest()->isPost()) {
            try {
                Service_Image::removeImage($image);

                $this->getResponse()->setBody('ok')->sendResponse();
                exit;
            } catch (Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
                Service_Logger::get()->err($e->getMessage() . ' ' . $e->getTraceAsString());
            }
            $this->getResponse()->setBody('Data is not valid!.')->sendResponse();
            exit;
        }
    }

    /**
     * @return Engine_Doctrine_Record_IdentifiableInterface
     */
    private function getItem()
    {
        $id = trim($this->getRequest()->getParam('id') . '');
        $field = null;
        if (!is_numeric($id)) {
            $field = 'hash';
        }

        $class = $this->getRequest()->getParam('class');

        $this->forward403Unless(Zend_Loader_Autoloader::getInstance()->autoload($class));
        $object = new $class();
        $this->forward403Unless($object instanceof Engine_Doctrine_Record_IdentifiableInterface);
        $this->forward403Unless($object instanceof Doctrine_Record);
        $table = $object->getTable();

        if (empty($field)) {
            $pks = $table->getIdentifierColumnNames();
            $field = $pks[0];
        }

        $item = $table->findOneBy($field, $id);

        $this->forward404Unless($item, 'Object NOT Exists (' . $id . ')');
        $this->forward403Unless($this->getSelectedBaseUser()->hasAccess($item));

        return $item;
    }
}
