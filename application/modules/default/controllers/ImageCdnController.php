<?php

class ImageCdnController extends Zend_Controller_Action
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $resize;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $params;

    /**
     * @return Image|null
     */
    private $image = null;

    public function init()
    {
        ob_get_clean();

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $this->id = (int)$this->getRequest()->getParam('id');
        $this->key = $this->getRequest()->getParam('key');
        $this->resize = $this->getRequest()->getParam('resize');
        $this->width = (int)$this->getRequest()->getParam('width');
        $this->height = (int)$this->getRequest()->getParam('height');
        $this->format = $this->getRequest()->getParam('format');
        $this->params = $this->getRequest()->getParam('params');

        $this->image = Service_Image::getImage($this->id);

        if ($this->image === null) {
            throw new Zend_Controller_Action_Exception('Image not found', 404);
        }

        $fileImage = ROOT_PATH . $this->image->file_path;

        if (!file_exists($fileImage)) {
            throw new Zend_Controller_Action_Exception('Image not found: (' . $fileImage . ')', 404);
        }

        $params = [
            'id' => $this->id,
            'resize' => $this->resize,
            'width' => $this->width,
            'height' => $this->height,
            'format' => $this->format,
            'params' => $this->params,
        ];

        if (DEBUG === false && $this->key !== Image::getHashKey($params)) {
            throw new Zend_Controller_Action_Exception('Image not found', 404);
        }

        $this->getRequest()->setParam('image', $this->image);
        $this->_helper->cache(['serve'], ['images', 'image_' . $this->id, 'object_' . $this->image->object_id, 'class_' . $this->image->class_name]);
    }

    /**
     * Show image by params.
     *
     * All params validation - in router regural expression.
     */
    public function serveAction()
    {
        $paramsEx = array_filter(explode('-', $this->params));

        $fileImage = ROOT_PATH . $this->image->file_path;
        $params = $this->image->getParams(['width' => $this->width, 'height' => $this->height, 'resize' => $this->resize]);

        foreach ($paramsEx as $v) {
            $params[$v[0]] = mb_substr($v, 1);
        }

        $imageService = Engine_Image::factory();
        $imageService->open($fileImage);
        $image_modify = false;

        $this->getResponse()
            ->setHeader('Content-type', $imageService->getMime(), true)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'max-age=86400', true)
            ->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 86400), true);

        if (Image::RESIZE_ORYGINAL === $this->resize) {
            echo file_get_contents($fileImage);

            return;
        }

        if (isset($params[Image::PARAM_CROP]) && Image::RESIZE_ORYGINAL !== $this->resize) {
            $cparams = explode(',', $params[Image::PARAM_CROP]);
            $imageService->crop($cparams[0], $cparams[1], $cparams[2], $cparams[3]);
            $image_modify = true;
        }

        $fileImageTmp = null;
        if (isset($params[Image::PARAM_PERSPECTIVE]) && Image::RESIZE_ORYGINAL !== $this->resize) {
            if ($image_modify) {
                $imageService->save($fileImage);
            }

            $imageService = Engine_Image::factory('Engine_Image_Adapter_ImageMagick');
            $imageService->open($fileImage);
            $imageService->generatePerspective('[' . $params[Image::PARAM_PERSPECTIVE] . ']');
            $fileImage = $fileImageTmp = APPLICATION_TMP . DS . $this->image->getId() . '_' . Engine_Utils::_()->getShortHash() . '.' . $this->format;
            $imageService->save($fileImage);

            $imageService = Engine_Image::factory();
            $imageService->open($fileImage);
            $image_modify = true;
        }

        // if image not yet modity and oryginal image is smaller then teturn size, then
        // return oryginal image
        if (!$image_modify && $this->width >= $imageService->getWidth() && $this->height >= $imageService->getHeight()) {
            echo file_get_contents($fileImage);

            return;
        }

        if (Image::RESIZE_TO_WIDTH === $this->resize) {
            $imageService->resize($this->width, 3000);
        } elseif (Image::RESIZE_TO_HEIGHT === $this->resize) {
            $imageService->resize(3000, $this->height);
        } elseif (Image::RESIZE_TO_BOTH === $this->resize) {
            $imageService->resize($this->width, $this->height);
        } elseif (Image::RESIZE_TO_BOTH_IF_BIGGER === $this->resize) {
            $imageService->resizeIfBigger($this->width, $this->height);
        } elseif (Image::RESIZE_TO_BOTH_AND_FILL === $this->resize) {
            $imageService->resize($this->width, $this->height);
        } elseif (Image::RESIZE_ADAPTIVE === $this->resize || Image::RESIZE_CROP === $this->resize) {
            $imageService->resizeOut($this->width, $this->height);
            $imageService->cropFromCenter($this->width, $this->height);
        }

        if (null !== $fileImageTmp) {
            @unlink($fileImageTmp);
        }

        echo $imageService . '';
    }

    public function mainAction()
    {
        throw new Zend_Controller_Action_Exception('Image not found', 404);
    }
}
