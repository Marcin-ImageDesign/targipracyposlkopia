<?php

class ImageCropperController extends Engine_Controller_Admin
{
    /**
     * @var Image
     */
    private $_image;

    public function preDispatch()
    {
        parent::preDispatch();

        $token = $this->getRequest()->getParam('token');
        $id_image = $this->getRequest()->getParam('id_image');

        $validToken = Service_SecurityToken::get($id_image, 'image_cropper');

        if ($validToken !== $token) {
            throw new Zend_Controller_Action_Exception('Invalid token', 403);
        }
    }

    /**
     * Strona html - wyświetla widget do wyboru fragmentu zdjęcia.
     * Zdjęcie jest przekazane w parametrze 'img', który jest ścieżką do pliku
     * od katalogu publicznego.
     */
    public function indexAction()
    {
        $this->_image = Image::find($this->getParam('id_image'));
//        $imagePath = '/'.ltrim(base64_decode($this->getRequest()->getParam('path').''), '/');
        if (!$this->_image) {
            throw new Zend_Controller_Action_Exception("Image path '{$this->getParam('id_image')}' not exists", 404);
        }

        $this->view->imagePath = $this->_image->getOrginalUrl();
        $this->view->selection = [50, 50, 500, 500];
        $this->view->imageParams = $this->getImageParams();

        $this->_helper->layout()->setLayout('layout-image-cropper');

        if ($this->getRequest()->isXmlHttpRequest()) {
//            $config = $this->getRequest()->getParam('config');
            $cords = (array) $this->getRequest()->getParam('cords');
            $cords = array_map('intval', $cords);

            $params = [Image::PARAM_CROP => $cords['x'] . ',' . $cords['y'] . ',' . $cords['w'] . ',' . $cords['h']];

            try {
                $this->_image->setParams($params);
                $this->_image->save();
//                Service_Image::cleanCache($this->_image);
                $this->_helper->json(['status' => 1]);
            } catch (Exception $e) {
                $this->_helper->json(['status' => 0, 'message' => $e->getMessage()]);
            }
        }
    }

    private function getImageParams()
    {
        $imageParams = $this->getRequest()->getParam('imageParams');

        $params = [];

        if (!empty($imageParams)) {
            $imageParams = json_decode($imageParams, true);
            if (false !== $imageParams) {
                $params = $imageParams;
            }
        }

        return $params;
    }

    /**
     * Zwraca absolutny adres do relatywnego $path.
     *
     * Aktualnie wyszukuje tylko w katalogu publicznym aplikacji.
     *
     * @param string $path Relatywny adres do pliku (publiczny URL)
     *
     * @return null|string Null jeżeli plik nie istnieje
     */
    private function getImageAbsolutePath($path)
    {
        $imageAbsolutePath = APPLICATION_WEB . DIRECTORY_SEPARATOR . ltrim($path, '/');

        if (!file_exists($imageAbsolutePath)) {
            return null;
        }

        return $imageAbsolutePath;
    }

    /**
     * Zwraca adres absolutny do pliku, który zostanie nadpisany wprowadzonymi zmianami (kadrowaniem).
     *
     * Sposób działania:
     *
     * 1. /a/b/c/file_name.jpg.source.jpg  => /a/b/c/file_name.jpg
     * 2. /a/b/c/file_name.jpg             => /a/b/c/file_name.jpg
     *
     * Czyli jeżeli adres wejściowy posiada drugi
     * segment (separator '.' kropka) równy 'source' (przypadek 1.)
     * zostanie zwrócony adres pozbawiony '.source.[ext]',
     * w przeciwnym wypadku zostanie zwrócony adres wejściowy.
     *
     * @param string $absolutePath Absolutny adres do pliku
     *
     * @return string
     */
    private function getImageAbsoluteOutputPath($absolutePath)
    {
        $p = explode('.', $absolutePath);

        array_pop($p); // ext
        $source = array_pop($p);

        if ('source' !== $source) {
            return $absolutePath;
        }

        return implode('.', $p);
    }
}
