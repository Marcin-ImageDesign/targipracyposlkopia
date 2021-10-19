<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marek Skotarek
 * Date: 25.06.13
 * Time: 13:41
 * To change this template use File | Settings | File Templates.
 */
class Developers_LogController extends Engine_Controller_Admin
{
    private $_fileList;

    public function postDispatch()
    {
        parent::postDispatch();

        $this->_fileList = scandir(APPLICATION_TMP . '/logs/');
        $this->view->fileList = $this->_fileList;

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('log/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_developers_log-index'));
    }

    public function logsAction()
    {
        // get file
        $dir = APPLICATION_TMP . '/logs/';
        $fileName = $this->_getParam('file');
        $read = 2097152;

        if (file_exists($dir . $fileName)) {
            $size = filesize($dir . $fileName);
            $fromRead = ($size > $read) ? ($size - $read) : 0;
            $content = file_get_contents($dir . $fileName, null, null, $fromRead, $read);
            $this->view->content = $content;
        }
        $this->view->placeholder('headling_1_content')->set($this->view->translate($fileName));
    }
}
