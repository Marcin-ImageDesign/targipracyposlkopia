<?php

class Event_AdminStandFileController extends Engine_Controller_Admin
{
    /**
     * @var \ExhibStand|mixed
     */
    public $exhib_stand;
    /**
     * @var \ExhibStandFile|mixed
     */
    public $exhib_stand_file;
    /**
     * @var \Event_Form_StandFile|mixed
     */
    public $formStandFile;
    /**
     * @var mixed[]|mixed|null
     */
    public $file;
    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-stand-file/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->exhib_stand = ExhibStand::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser(), $this->getSelectedLanguage());
        //sprawdzenie dostępu do elementu
        $this->checkExhibStandAccess();

        $standFileListQuery = Doctrine_Query::create()
            ->from('ExhibStandFile sf')
            ->where('sf.id_exhib_stand = ?', $this->exhib_stand->getId())
            ->orderBy('sf.name ASC')
        ;

        $pager = new Doctrine_Pager($standFileListQuery, $this->_getParam('page', 1), 20);
        $standFileList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('File list'));
        $this->view->exhib_stand = $this->exhib_stand;
        $this->view->standFileList = $standFileList;
    }

    public function newAction()
    {
        $this->exhib_stand = ExhibStand::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser(), $this->getSelectedLanguage());
        //sprawdzenie dostępu do elementu
        $this->checkExhibStandAccess();

        $this->exhib_stand_file = new ExhibStandFile();
        $this->exhib_stand_file->ExhibStand = $this->exhib_stand;
        $this->exhib_stand_file->hash = $this->engineUtils->getHash();
        $this->exhib_stand_file->CreatorUser = $this->userAuth;
        $this->exhib_stand_file->BaseUser = $this->getSelectedBaseUser();

        $this->formStandFile();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New file'));
    }

    public function editAction()
    {
        //pobranie elementu po polu hash
        $this->exhib_stand_file = ExhibStandFile::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser(), $this->getSelectedLanguage());
        //sprawdzenie dostępu
        $this->checkExhibStandFileAccess();
        //wyslnie danych do prywatnej funkcji generującej formularz
        $this->formStandFile();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set('Edit file');
    }

    public function downloadAction()
    {
        $this->exhib_stand_file = ExhibStandFile::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser());
        $this->forward404Unless($this->exhib_stand_file, 'ExhibStandFile NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->exhib_stand_file),
            [$this->getSelectedBaseUser()->getId(), $this->exhib_stand_file->getId()]
        );
        $filename = $this->exhib_stand_file->uri . '.' . $this->exhib_stand_file->file_ext;
        $file_path = $this->exhib_stand_file->getDownloadFile();

        if (!empty($file_path) && file_exists($file_path)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($file_path);
        } else {
            echo 'The specified file does not exist : ' . $filename . '.' . $this->exhib_stand_file->file_ext;
        }
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function deleteAction()
    {
        //pobranie elementu po polu hash
        $this->exhib_stand_file = ExhibStandFile::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser());
        //sprawdzenie dostępu
        $this->checkExhibStandFileAccess();
        //usuwanie pliku
        $this->exhib_stand_file->deleteFile();
        //usuwanie recordu w db
        $this->exhib_stand_file->delete();
        //zerowanie licznika plików
        $this->exhib_stand_file->ExhibStand->count_files = null;
        $this->exhib_stand_file->ExhibStand->save();

        $this->_flash->success->addMessage('File has been deleted.');
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit(['hash' => $this->exhib_stand_file->ExhibStand->getHash(), 'event_hash' => $this->view->selected_event_hash], 'event_admin-stand-files_index');
    }

    private function checkExhibStandAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->exhib_stand, 'ExhibStand NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->exhib_stand),
            [$this->getSelectedBaseUser()->getId(), $this->exhib_stand->getId()]
        );

        $this->forward403Unless(
            $this->userAuth->hasAccess(null, $this->exhib_stand)
        );
    }

    private function checkExhibStandFileAccess()
    {
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->exhib_stand_file, 'ExhibStandFile NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->exhib_stand_file),
            [$this->getSelectedBaseUser()->getId(), $this->exhib_stand_file->getId()]
        );
        $this->forward403Unless(
            $this->userAuth->hasAccess(null, $this->exhib_stand_file->ExhibStand)
        );
    }

    private function formStandFile()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('admin-stand-file/form-file', null, true);
        //pobranie formularza dla pojedyńczego elementu
        $this->formStandFile = new Event_Form_StandFile($this->exhib_stand_file);
        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->formStandFile->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->standFileGetRequest();
            //zapis
            $this->exhib_stand_file->save();
            //zerowanie licznika plików
            $this->exhib_stand_file->ExhibStand->count_files = null;
            $this->exhib_stand_file->ExhibStand->save();
            //zapis pliku z id
            $this->saveFile();
            //Tworzenie informacji zwrotnej
            $this->_flash->success->addMessage('Save successfully completed');
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->exhib_stand_file->getHash(), 'event_hash' => $this->view->selected_event_hash], 'event_admin-stand-files_edit');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->formStandFile = $this->formStandFile;
        $this->view->exhib_stand = $this->exhib_stand_file->ExhibStand;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function standFileGetRequest()
    {
        if (!$this->getSelectedBaseUser()->isDefaultLanguage($this->getSelectedLanguage()) && !isset($this->exhib_stand_file->ExhibStandFileLanguageOne)) {
            $this->exhib_stand_file->ExhibStandFileLanguageOne = new ExhibStandFileLanguage();
            $this->exhib_stand_file->ExhibStandFileLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }
        $this->exhib_stand_file->name = $this->formStandFile->main->name->getValue();
        $this->exhib_stand_file->setDescription($this->formStandFile->main->description->getValue());
        $this->exhib_stand_file->is_visible = (bool) $this->formStandFile->is_visible->getValue();
        if ($this->formStandFile->is_published) {
            $this->exhib_stand_file->is_published = (bool) $this->formStandFile->is_published->getValue();
        }
        $this->exhib_stand_file->uri = $this->engineUtils->getFriendlyUri($this->formStandFile->main->name->getValue());

        $upload = new Zend_File_Transfer_Adapter_Http();
        $this->file = $upload->getFileInfo();

        //zapis obrazka
        if (!empty($this->file) && isset($this->file['image']) && 4 !== $this->file['image']['error']) {
            $this->exhib_stand_file->deleteImage();

            $tmp_name = $this->file['image']['tmp_name'];
            $imageInfo = getimagesize($tmp_name);
            $image_ext = $this->engineUtils->getFileExt($this->file['image']['name']);
            $this->exhib_stand_file->setImageExt($image_ext);

            $image = Engine_Image::factory();
            $image->open($tmp_name);
            $image->resizeIfBigger(EventFile::IMAGE_WIDTH, EventFile::IMAGE_HEIGHT);
            $image->save($this->exhib_stand_file->getAbsoluteImage());
        }

        if (!empty($this->file) && isset($this->file['file']) && 4 !== $this->file['file']['error']) {
            // zapis pliku
            $this->exhib_stand_file->deleteFile();
            $this->exhib_stand_file->file_ext = $this->engineUtils->getFileExt($this->file['file']['name']);
        } else {
            $this->file = null;
        }
    }

    private function saveFile()
    {
        if (!empty($this->file) && !empty($this->exhib_stand_file)) {
            $tmp_name = $this->file['file']['tmp_name'];
            if ($this->exhib_stand_file->isGraphical()) {
                // zapis pliku graficznego - tworzymy miniaturkę
                $image_thumb = Engine_Image::factory();
                $image_thumb->open($tmp_name);

                $thumbMaxWidth = $this->getSelectedBaseUser()->getVariable(Variable::EXHIB_STAND_FILE_THUMB_WIDTH);
                $thumbMaxHeight = $this->getSelectedBaseUser()->getVariable(Variable::EXHIB_STAND_FILE_THUMB_HEIGHT);

                $image_thumb->resizeIfBigger($thumbMaxWidth, $thumbMaxHeight);
                $image_thumb->save($this->exhib_stand_file->getRelativeFileThumb());
            }
            move_uploaded_file($tmp_name, $this->exhib_stand_file->getRelativeFile());
        }
    }
}
