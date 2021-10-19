<?php

class ErrorController extends Zend_Controller_Action
{
    private $logger;

    private $_error;

    public function errorAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_error = $this->_getParam('error_handler');
        if (true === @DEBUG) {
            $this->develHandler();
        } else {
            $this->productionHandler();
        }
    }

    private function develHandler()
    {
        $exception = $this->_error['exception'];
        echo '<b>' . $exception->getMessage() . '</b><br/>';
        echo $exception->getFile() . ':' . $exception->getLine() . '<br/>';
        echo get_class($exception) . '<br/>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
    }

    private function productionHandler()
    {
        $exception = $this->_error['exception'];

        if (404 !== $exception->getCode() && 403 !== $exception->getCode()) {
            $this->error503Apache();
        } else {
            $this->error404Apache();
        }
    }

    private function error404Apache()
    {
        $this->_response->setRawHeader('HTTP/1.1 404 Not Found');
        $baseUser = Zend_Registry::get('BaseUser');

        $file404 = $baseUser->getPublicRelativePath() . DS . '404.html';
        if (!file_exists($file404)) {
            $file404 = APPLICATION_WEB . DS . '404.html';
        }
        $this->_response->setBody(file_get_contents($file404));
        $this->_response->sendResponse();
    }

    private function error503Apache()
    {
        $this->_response->setRawHeader('HTTP/1.1 503 Service Unavailable');
        $baseUser = Zend_Registry::get('BaseUser');

        $file503 = $baseUser->getPublicRelativePath() . DS . '503.html';
        if (!file_exists($file503)) {
            $file503 = APPLICATION_WEB . DS . '503.html';
        }

        $this->_response->setBody(file_get_contents($file503));
        $this->_response->sendResponse();
    }
}
