<?php

include_once __DIR__ . '/Engine/Controller/Admin.php';

class Filemanager_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \Engine_Error|mixed
     */
    public $error;
    /**
     * @var bool
     */
    public $displayLayoutCalendar;
    /**
     * @var bool
     */
    public $hasLeftCol;
    /**
     * @var bool
     */
    public $fb_link;
    /**
     * @var \Zend_File_Transfer_Adapter_Http|mixed
     */
    public $upload;
    /**
     * @var mixed[]|mixed
     */
    public $files;
    public $sessionFiles;
    const VALIDATE_FILE_SHORT = '/[^a-zA-Z0-9\.\-\_]/';
    const VALIDATE_FILE = '/[^a-zA-Z0-9ęóąśłżźćńĘÓĄŚŁŻŹĆŃ\(\) \.\-\_]/';

    // Ścieżka od katalogu strony

    private $path;

    // Ścieżka absolutna strony na serwerze
    private $pathSite;

    // Ścieżka absolutna strony na serwerze + katalogu
    private $pathAbsolute;

    // scieżka do pliku dla przeglądarki
    private $pathBrowser;

    // zawartość aktualnie wybranego katalogu z $this->path
    private $catalogContent;

    /**
     * @var Engine_Utils
     */
    private $utils;

    /**
     * @var Engine_Error
     */
    private $_error;
    private $selectedFiles = ['operation' => '', 'files_operation' => '', 'files_path' => '', 'files' => [], 'showOverwriteForm' => false];
    private $allowExt = [];
    private $denyExt = ['exe', 'php', 'php5', 'php4'];
    private $imgExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
    private $form = [];
    private $mode = 'list';

    public function preDispatch()
    {
        parent::preDispatch();

        $this->getPath();
        $this->utils = Engine_Utils::getInstance();
        $this->error = Engine_Error::getInstance();
        $this->getSelectedFiles();
        $this->catalogContent = $this->getCatalogContent();

        $this->displayLayoutCalendar = false;
        $this->hasLeftCol = false;
        $this->fb_link = false;

        // dla formularza z plikiem
        if ($this->_getParam('isXmlHttpRequest', 0)) {
            $this->_helper->layout->disableLayout();
        }

        if (($this->_request->isXmlHttpRequest() || $this->_getParam('isXmlHttpRequest', 0)) && $this->_request->isPost()) {
            $this->_helper->viewRenderer->setNoRender();
            $this->displayJsonResponse = true;
        }

        $this->mode = $this->_getParam('mode', 'list');
        if ('get' === $this->mode && $this->_helper->layout->isEnabled()) {
            $this->_helper->layout->setLayout('layout_empty');
        }

        $this->view->path = $this->path;
        $this->view->mode = $this->mode;
        $this->view->headLink()->appendStylesheet('/_css/admin/filemenager.css');
    }

    public function postDispatch()
    {
        parent::postDispatch();

        $this->view->renderToPlaceholder('admin/_nav-left.phtml', 'nav-left');
        $this->view->error = $this->error;
    }

    public function indexAction()
    {
        $viewMode = null;
        $dirUp = $this->dirUp($this->path);
        $breadcrumbPath = trim($this->path, '/');
        $breadcrumbPath = empty($breadcrumbPath) ? [] : explode('/', $breadcrumbPath);

        // ustawienie widoku listy
        if ($this->_hasParam('view')) {
            $viewMode = $this->_getParam('view', 'list');
        } elseif ($this->_session->__isset('public_list_view')) {
            $viewMode = $this->_session->__get('public_list_view');
        }

        if (empty($viewMode) || !in_array($viewMode, ['list', 'tiles'], true)) {
            $viewMode = 'list';
        }
        $this->_session->__set('public_list_view', $viewMode);

        $this->view->headLink()->appendStylesheet('/_css/admin/filemenager.css');
        $this->view->headScript()->appendFile('/_js/admin/filemenager.js');
        $this->view->headScript()->appendFile('/_js/jquery/jquery.rightClick.js');
        //		$this->view->headLink()->appendStylesheet('/_css/jquery/jquery.Jcrop.css');
        //		$this->view->headScript()->appendFile('/_js/jquery/jquery.Jcrop.js' );

        $this->view->catalogContent = $this->catalogContent;
        $this->view->dirUp = $dirUp;
        $this->view->browserPath = $this->pathBrowser;
        $this->view->breadcrumbPath = $breadcrumbPath;
        $this->view->viewMode = $viewMode;
        $this->view->selectedFiles = $this->selectedFiles;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('File menager'));
    }

    public function fileInfoAction()
    {
        $path = $this->_getParam('path');
        $filename = $this->_getParam('file');
        $filePath = $this->pathAbsolute . DS . $filename;
        $fileInfo = [];

        $fileInfo['exists'] = file_exists($filePath);
        if ($fileInfo['exists']) {
            $fileInfo['file'] = $filename;
            $fileInfo['path'] = 'http://' . DOMAIN . $this->pathBrowser . '/' . $filename;
            $fileInfo['type'] = is_dir($filePath) ? 'dir' : 'file';
            $fileInfo['modificated'] = filemtime($filePath);

            if ('dir' === $fileInfo['type']) {
                $fileInfo['isImage'] = false;
            //				$fileInfo['countContent'] = $this->countElementsInsideDir($path.$fileName);
            } else {
                $fileInfo['size'] = filesize($filePath);
                $fileInfo['ext'] = mb_strtolower($this->utils->getFileExt($filePath));
                $fileInfo['isImage'] = in_array(mb_strtolower($fileInfo['ext']), $this->imgExt, true);
                if ($fileInfo['isImage']) {
                    $imgsize = getimagesize($filePath);
                    $fileInfo['width'] = $imgsize[0];
                    $fileInfo['height'] = $imgsize[1];
                    $fileInfo['preview'] = $fileInfo['path'];
                }
            }
        }

        $this->view->fileInfo = $fileInfo;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Properties'));
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    // akcja edycji obrazu

    public function editImageAction()
    {
        $path = $this->_getParam('path');
        $filename = $this->_getParam('filename');
        $filePath = trim($this->pathAbsolute, DS) . DS . $filename;
        $extOfFile = $this->utils->getFileExt($filename);
        $imageInfo = getimagesize($filePath);

        $this->view->headLink()->appendStylesheet('/_css/jquery/jquery.Jcrop.css');
        $this->view->headScript()->appendFile('/_js/jquery/jquery.Jcrop.js');

        if ($this->_request->isPost()) {
            if ($this->_hasParam('crop')) {
                if (false === $this->error->isErrors()) {
                    $image = Engine_Image::factory();
                    $image->open($filePath);
                    $image->crop($this->_getParam('selectionX'), $this->_getParam('selectionY'), $this->_getParam('selectionWidth'), $this->_getParam('selectionHeight'));
                    $image->save($filePath);

                    $this->_flash->success->addMessage('Save successfully completed');

                    $url = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->path;
                    if ($this->displayJsonResponse) {
                        $this->jsonResult['result'] = true;
                        $this->jsonResult['addJsFunction'] = ['goToUrlAfterResponse'];
                        $this->jsonResult['url'] = $url;
                    } else {
                        $this->_redirector->gotoUrlAndExit($url);
                    }
                }
            } elseif ($this->_hasParam('resize')) {
                $v_int = new Zend_Validate_Int();
                if (!$v_int->isValid($this->_getParam('resizeWidth'))) {
                    $this->error->addError('resizeWidth', 'wysokość i szerokość muszą być liczbami całkowitymi');
                }
                if (!$v_int->isValid($this->_getParam('resizeHeight'))) {
                    $this->error->addError('resizeHeight', 'wysokość i szerokość muszą być liczbami całkowitymi');
                }

                if (false === $this->error->isErrors()) {
                    $image = Engine_Image::factory();
                    $image->open($filePath);
                    $image->resizeIn($this->_getParam('resizeWidth'), $this->_getParam('resizeHeight'));
                    $image->save();
                    $this->_flash->success->addMessage('Save successfully completed');

                    $url = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->path;
                    if ($this->displayJsonResponse) {
                        $this->jsonResult['result'] = true;
                        $this->jsonResult['addJsFunction'] = ['goToUrlAfterResponse'];
                        $this->jsonResult['url'] = $url;
                    } else {
                        $this->_redirector->gotoUrlAndExit($url);
                    }
                } elseif ($this->displayJsonResponse) {
                    $this->jsonResult['result'] = false;
                    $this->jsonResult['error'] = $this->error->getErrors();
                }
            }
        }

        //		$this->view->filename = $filename;
        $this->view->filename = $filename;
        $this->view->extOfFile = $extOfFile;
        $this->view->filePath = $filePath;
        $this->view->imageInfo = $imageInfo;
        $this->view->pathBrowser = $this->pathBrowser;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Picture edit'));
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    public function newCatalogAction()
    {
        $this->form = ['catalog_name' => ''];
        $this->_helper->viewRenderer('form-new-catalog');
        if ($this->_request->isPost()) {
            $this->form['catalog_name'] = trim($this->_getParam('catalog_name'));

            $this->validateCatalogForm();
            if ($this->error->isValid()) {
                $this->saveCatalogForm();

                $this->_flash->succeess->addMessage('Save successfully completed');
                $url = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->path;

                if ($this->displayJsonResponse) {
                    $this->jsonResult['result'] = true;
                    $this->jsonResult['addJsFunction'] = ['goToUrlAfterResponse'];
                    $this->jsonResult['url'] = $url;
                } else {
                    $this->_redirector->gotoRouteAndExit(['mode' => $this->mode], 'site_files');
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['result'] = false;
                $this->jsonResult['error'] = $this->error->getErrors();
            }
        }

        $this->view->form = $this->form;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New folder'));
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    public function nameEditAction()
    {
        $this->_helper->viewRenderer('form-name-edit');
        $this->form = ['element' => '', 'element_is_file' => false, 'element_name' => '',
            'element_ext' => '', 'element_path' => '', 'new_element_name' => '', ];

        $this->form['element'] = trim($this->_getParam('element'), '/');
        $this->form['element_name'] = trim(mb_substr($this->form['element'], mb_strrpos($this->form['element'], '/')), '/');
        $this->form['element_path'] = str_replace($this->form['element_name'], '', $this->form['element']);
        $filename = $this->pathSite . DS . $this->form['element'];

        if (!file_exists($filename) || 0 === mb_strlen($this->form['element'])) {
            $this->_flash->error->addMessage('Item not exists');
            $url = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->form['element_path'];
            $this->_redirector->gotoUrlAndExit($url, ['prependBase' => false]);
        }

        if (is_file($filename)) {
            $this->form['element_ext'] = mb_substr($this->form['element_name'], mb_strrpos($this->form['element_name'], '.'));
            $this->form['element_name'] = mb_substr($this->form['element_name'], 0, mb_strrpos($this->form['element_name'], '.'));
            $this->form['element_is_file'] = true;
        }

        $this->form['new_element_name'] = $this->form['element_name'];

        if ($this->_request->isPost()) {
            $this->form['new_element_name'] = trim($this->_getParam('new_element_name'));

            $this->validateElementNameForm();
            if ($this->error->isValid()) {
                if ($this->form['new_element_name'] !== $this->form['element_name']) {
                    $oldname = $this->pathSite . DS . ltrim($this->form['element_path'] . DS, '/') . $this->form['element_name'] . $this->form['element_ext'];
                    $newname = $this->pathSite . DS . ltrim($this->form['element_path'] . DS, '/') . $this->form['new_element_name'] . $this->form['element_ext'];
                    rename($oldname, $newname);
                    $this->_flash->success->addMessage('Save successfully completed');
                } else {
                    $this->_flash->error->addMessage('Name not changed');
                }

                $url = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->form['element_path'];
                if ($this->displayJsonResponse) {
                    $this->jsonResult['result'] = true;
                    $this->jsonResult['addJsFunction'] = ['goToUrlAfterResponse'];
                    $this->jsonResult['url'] = $url;
                } else {
                    $this->_redirector->gotoUrlAndExit($url, ['prependBase' => false]);
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['result'] = false;
                $this->jsonResult['error'] = $this->error->getErrors();
            }
        }

        $this->view->form = $this->form;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit name'));
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    public function uploadFileAction()
    {
        $this->form = ['file_name' => '', 'file_path' => ''];
        $this->_helper->viewRenderer('form-upload-file');
        if ($this->_request->isPost()) {
            $this->upload = new Zend_File_Transfer_Adapter_Http();
            $this->files = $this->upload->getFileInfo();

            $this->validateUploadFileForm();
            if ($this->error->isValid()) {
                $this->upload->addFilter('Rename', ['target' => $this->form['file_path'], 'overwrite' => false], 'upload_file');
                $this->upload->receive('upload_file');

                $this->_flash->addMessage(['msg-ok', 'Plik został wysłany']);
                $url = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->path;
                if ($this->displayJsonResponse) {
                    $this->jsonResult['result'] = true;
                    $this->jsonResult['addJsFunction'] = ['goToUrlAfterResponse'];
                    $this->jsonResult['url'] = $url;
                } else {
                    $this->_redirector->gotoUrlAndExit($url, ['prependBase' => false]);
                }
            } elseif ($this->displayJsonResponse) {
                $this->jsonResult['result'] = false;
                $this->jsonResult['error'] = $this->error->getErrors();
            }
        }

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Send new file'));
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    //=====================================
    //		SELECTED FILES OPERATIONS
    //=====================================

    /**
     * ackja obslugujaca formularze post z operacjami na zaznaczonych plikach.
     */
    public function operationAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->setSelectedFiles();

        //		if( $this->_request->isPost() ){
        if ('delete' === $this->selectedFiles['operation']) {
            $this->deleteFiles();
        } elseif ('paste' === $this->selectedFiles['operation']) {
            if ('copy' === $this->selectedFiles['files_operation']) {
                $this->pasteFiles();
            } elseif ('cut' === $this->selectedFiles['files_operation']) {
                $this->renameFiles();
            }
        } elseif ('cancel' === $this->selectedFiles['operation']) {
            $this->setEmptySelectedFiles();
        }
        //		}

        $gotoUrl = $this->view->url(['mode' => $this->mode], 'site_files') . '?path=' . $this->path;
        $this->_redirector->gotoUrlAndExit($gotoUrl, ['prependBase' => false]);
    }

    //=====================================
    //		OTHER NEEDED FUNCTION
    //=====================================

    protected function getCatalogContent()
    {
        $k = null;
        $catalogContent = [];
        if (is_dir($this->pathAbsolute)) {
            $dh = opendir($this->pathAbsolute);
            if ($dh) {
                while (false !== ($file = readdir($dh))) {
                    if ('.' !== $file && '..' !== $file && '.' !== mb_substr($file, 0, 1)) {
                        $pos = ['file' => $file, 'type' => '', 'is_image' => false,
                            'ext' => '', 'name' => '', 'size' => '', 'modificated' => '', ];

                        if (is_dir($this->pathAbsolute . DS . $file)) {
                            $pos['type'] = 'dir';
                            $pos['name'] = $pos['file'];
                            $pos['ext'] = 'folder';
                        } else {
                            $pos['type'] = 'file';
                            $pos['ext'] = $this->utils->getFileExt($file);
                            $pos['name'] = str_replace('.' . $pos['ext'], '', $pos['file']);
                            $pos['size'] = filesize($this->pathAbsolute . DS . $file);
                            $pos['is_image'] = in_array($pos['ext'], $this->imgExt, true);

                            if ($pos['size'] < 1024) {
                                $k = 'B';
                            } elseif ($pos['size'] < (1024 * 1024)) {
                                $k = 'KB';
                                $pos['size'] /= 1024;
                            } elseif ($pos['size'] < (1024 * 1024 * 1024)) {
                                $k = 'MB';
                                $pos['size'] /= 1024 * 1024;
                            } elseif ($pos['size'] < (1024 * 1024 * 1024 * 1024)) {
                                $k = 'GB';
                                $pos['size'] /= 1024 * 1024 * 1024;
                            }
                            $pos['size'] = round($pos['size'], 2) . ' ' . $k;
                        }
                        $pos['modificated'] = filemtime($this->pathAbsolute . DS . $file);
                        $catalogContent[$pos['name'] . ('file' === $pos['type'] ? '.' . $pos['ext'] : '')] = $pos;
                    }
                }
                closedir($dh);
            }
        }

        return $this->array_msort($catalogContent, ['type' => SORT_ASC, 'file' => SORT_ASC]);
    }

    protected function dirUp($path)
    {
        $dirup = $path;
        if ('/' === mb_substr($dirup, -1, 1)) {
            $dirup = mb_substr($dirup, 0, mb_strlen($dirup) - 1);
        }
        $n = mb_strrpos($dirup, '/');

        return mb_substr($dirup, 0, $n);
    }

    private function getPath()
    {
        $this->path = $this->_getParam('path', '');
        $this->path = trim($this->path, '/');
        $this->pathSite = $this->getSelectedBaseUser()->getPublicRelativeRepositoryPath(true);
        $this->pathAbsolute = rtrim($this->pathSite . DS . $this->path, '/');
        if (!is_dir($this->pathAbsolute)) {
            $this->path = '';
            $this->pathAbsolute = $this->pathSite;
        }
        $this->pathBrowser = rtrim($this->getSelectedBaseUser()->getPublicBrowserRepositoryPath() . '/' . $this->path, '/');

        return $this->path;
    }

    private function setSelectedFiles()
    {
        $avalibleOptions = [
            '' => '', $this->view->translate('copy') => 'copy', $this->view->translate('cut') => 'cut',
            $this->view->translate('delete') => 'delete', $this->view->translate('overwrite') => 'overwrite',
            $this->view->translate('paste') => 'paste', $this->view->translate('cancel') => 'cancel',
            'copy' => 'copy', 'cut' => 'cut', 'paste' => '', 'cancel' => 'cancel',
            'delete' => 'delete', ];

        $selectedOption = $this->_getParam('selectedOption');
        if (array_key_exists($selectedOption, $avalibleOptions)) {
            $this->selectedFiles['operation'] = $avalibleOptions[$selectedOption];
            if ('paste' !== $this->selectedFiles['operation']) {
                $this->selectedFiles['files_operation'] = $avalibleOptions[$selectedOption];
            }
        }

        if ($this->_hasParam('file')) {
            $filesList = $this->_getParam('file', []);
            if ('paste' !== $this->selectedFiles['operation']) {
                $this->selectedFiles['files'] = [];
            }
            foreach ($filesList as $file => $value) {
                if ($value) {
                    $this->selectedFiles['files'][$file] = false;
                }
            }
            if (count($this->selectedFiles['files']) > 0 && 'paste' !== $this->selectedFiles['operation']) {
                $this->selectedFiles['files_path'] = $this->path;
            }
        }

        $this->_session->__set('selectedFiles', serialize($this->selectedFiles));
    }

    private function getSelectedFiles()
    {
        if ($this->_session->__isset('selectedFiles')) {
            $this->selectedFiles = unserialize($this->_session->__get('selectedFiles'));
        }

        return $this->selectedFiles;
    }

    private function setEmptySelectedFiles()
    {
        $this->_session->__unset('selectedFiles');
    }

    private function validateCatalogForm()
    {
        if (0 === mb_strlen($this->form['catalog_name'])) {
            $this->error->addError('catalog_name', 'Podanie nazwy katalogu jest wymagane');
        } elseif (preg_match(self::VALIDATE_FILE_SHORT, $this->form['catalog_name'])) {
            $this->error->addError('catalog_name', 'Nazwa katalogu może zawierać znaki i liczby z przedziału: a-z, A-Z, 0-9, "_", "-"');
        } elseif (mb_strlen($this->form['catalog_name'], CHARSET) > 255) {
            $this->error->addError('catalog_name', 'Nazwa katalogu nie może być dłuższa niż 255 znaków');
        } else {
            // sprawdzenie czy katalog taki już nie istnieje
            $checkDir = $this->pathAbsolute . DS . $this->form['catalog_name'];
            if (file_exists($checkDir)) {
                $this->error->addError('catalog_name', 'Istnieje już plik lub katalog o podanej nazwie');
            }
        }
    }

    private function saveCatalogForm()
    {
        $pathname = $this->pathAbsolute . DS . $this->form['catalog_name'];
        mkdir($pathname, DIR_PRIVILIGES);
        chmod($pathname, DIR_PRIVILIGES);
    }

    private function validateElementNameForm()
    {
        if (0 === mb_strlen($this->form['new_element_name'])) {
            $this->error->addError('new_element_name', 'Podanie nazwy jest wymagane');
        } elseif (preg_match(self::VALIDATE_FILE_SHORT, $this->form['new_element_name'])) {
            $this->error->addError('new_element_name', 'Nazwa może zawierać znaki i liczby z przedziału: a-z, A-Z, 0-9, "_", "-"');
        } elseif (mb_strlen($this->form['new_element_name'], CHARSET) > 255) {
            $this->error->addError('new_element_name', 'Nazwa nie może być dłuższa niż 255 znaków');
        } elseif ($this->form['new_element_name'] !== $this->form['element_name']) {
            // sprawdzenie czy katalog taki już nie istnieje
            $filename = $this->pathSite . DS . ltrim($this->form['element_path'] . DS, '/') . $this->form['new_element_name'] . $this->form['element_ext'];
            if (file_exists($filename)) {
                $this->error->addError('new_element_name', 'Istnieje już plik lub katalog o podanej nazwie');
            }
        }
    }

    private function validateUploadFileForm()
    {
        if (empty($this->files) || !isset($this->files['upload_file']) || 4 === $this->files['upload_file']['error']) {
            $this->error->addError('upload_file', 'Nie wybrano pliku');
        } else {
            $ext = $this->utils->getFileExt($this->files['upload_file']['name']);

            if (!empty($this->allowExt) && !in_array($ext, $this->allowExt, true)) {
                $this->error->addError('upload_file', 'Dopuszczalne formaty plików to: ' . join(', ', $this->allowExt));
            } elseif (in_array($ext, $this->denyExt, true)) {
                $this->error->addError('upload_file', 'Format pliku "' . $ext . '" nie jest obsługiwany');
            }

            if ($this->files['upload_file']['size'] > MAX_FILE_SIZE) {
                $this->error->addError('upload_file', 'Podany plik jest zbyt wielki. Maksymalna wielkość pliku to: ' . round(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB.');
            }
        }

        // sprawdzenie czy taki plik istnieje
        if (!$this->error->isError('upload_file')) {
            $name = mb_substr($this->files['upload_file']['name'], 0, mb_strrpos($this->files['upload_file']['name'], '.'));
            $ext = mb_substr($this->files['upload_file']['name'], mb_strrpos($this->files['upload_file']['name'], '.'));
            $this->form['file_name'] = $this->utils->getFriendlyUri($name) . $ext;
            $filename = $this->form['file_name'];
            if (preg_match(self::VALIDATE_FILE_SHORT, $filename)) {
                $this->error->addError('upload_file', 'Nazwa pliku może zawierać znaki i liczby z przedziału: a-z, A-Z, 0-9, "_", "-"');
            } else {
                $this->form['file_path'] = $this->pathAbsolute . DS . $this->form['file_name'];
                if (file_exists($this->form['file_path'])) {
                    $this->error->addError('upload_file', 'Istnieje już plik lub katalog o podanej nazwie');
                }
            }
        }
    }

    private function pasteFiles()
    {
        $duplicate = false;
        $error = false;
        $engineFile = new Engine_File();

        foreach ($this->selectedFiles['files'] as $file => $overwrite) {
            if (!array_key_exists($file, $this->catalogContent) || true === $overwrite) {
                $elementFrom = $this->pathSite . DS . $this->selectedFiles['files_path'] . DS . $file;
                $elementTo = $this->pathAbsolute . DS . $file;
                if (is_file($elementFrom)) {
                    $engineFile->smartCopy($elementFrom, $elementTo);
                } elseif (is_dir($elementFrom)) {
                    if (false === mb_strstr($elementTo, $elementFrom)) {
                        $engineFile->smartCopy($elementFrom, $elementTo);
                    } else {
                        $this->_flash->addMessage(['msg-error', 'Katalog docelowy jest podkatalogiem katalogu źródłowego']);
                        $error = true;
                    }
                }
            } else {
                $this->sessionFiles['showOverwriteForm'] = true;
                $duplicate = true;
            }
        }

        //		if( $duplicate == false && $error == false ){
        $this->_flash->addMessage(['msg-ok', 'Pliki zostały skopiowane']);
        $this->setEmptySelectedFiles();
        //		}
    }

    private function renameFiles()
    {
        $duplicate = false;
        $error = false;
        if ($this->selectedFiles['files_path'] !== $this->path) {
            foreach ($this->selectedFiles['files'] as $file => $overwrite) {
                if (!array_key_exists($file, $this->catalogContent) || true === $overwrite) {
                    $elementFrom = $this->pathSite . DS . $this->selectedFiles['files_path'] . DS . $file;
                    $elementTo = $this->pathAbsolute . DS . $file;

                    if (is_dir($elementFrom)) {
                        if (false === mb_strstr($elementTo, $elementFrom)) {
                            rename($elementFrom, $elementTo);
                        } else {
                            $this->_flash->addMessage(['msg-error', 'Katalog docelowy jest podkatalogiem katalogu źródłowego']);
                            $error = true;
                        }
                    } else {
                        rename($elementFrom, $elementTo);
                    }
                } else {
                    $this->sessionFiles['showOverwriteForm'] = true;
                    $duplicate = true;
                }
            }
        } else {
            $this->_flash->addMessage(['msg-error', 'Nie można wkleić plików: ścieżka pliku źródłowego i docelowego są takie same']);
            $error = true;
        }

        $this->_flash->addMessage(['msg-ok', 'Pliki zostały przeniesione']);
        $this->setEmptySelectedFiles();
        //		}
    }

    private function deleteFiles()
    {
        $engineFile = new Engine_File();

        foreach (array_keys($this->selectedFiles['files']) as $file) {
            $pathAbsolute = $this->pathAbsolute . DS . $file;
            if (!file_exists($pathAbsolute)) {
                $error = 'Wskazany element nie istnieje';
            }
            if (is_dir($pathAbsolute)) {
                if (empty($error)) {
                    $engineFile->rmdir_recurse($pathAbsolute);
                    rmdir($pathAbsolute);
                }
            } elseif (is_file($pathAbsolute)) {
                unlink($pathAbsolute);
            }
        }

        $this->_flash->addMessage(['msg-ok', 'Usunięto wskazane elementy']);
        $this->setEmptySelectedFiles();
    }

    private function array_msort($array, $cols)  //multisort po parametrze tablicy
    {
        $colarr = [];
        foreach (array_keys($cols) as $col) {
            $colarr[$col] = [];
            foreach ($array as $k => $row) {
                $colarr[$col]['_' . $k] = mb_strtolower($row[$col]);
            }
        }
        $params = [];
        foreach ($cols as $col => $order) {
            $params[] = &$colarr[$col];
            $order = (array) $order;
            foreach ($order as $order_element) {
                //pass by reference, as required by php 5.3
                $params[] = &$order_element;
            }
        }
        call_user_func_array('array_multisort', $params);
        $ret = [];
        $keys = [];
        $first = true;
        foreach ($colarr as $col => $arr) {
            foreach (array_keys($arr) as $k) {
                if ($first) {
                    $keys[$k] = mb_substr($k, 1);
                }
                $k = $keys[$k];
                if (!isset($ret[$k])) {
                    $ret[$k] = $array[$k];
                }
                $ret[$k][$col] = $array[$k][$col];
            }
            $first = false;
        }

        return $ret;
    }
}
