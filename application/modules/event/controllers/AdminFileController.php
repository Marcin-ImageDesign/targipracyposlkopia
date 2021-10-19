<?php

class Event_AdminFileController extends Engine_Controller_Admin
{
    /**
     * @var \Event_Form_File_MailText|mixed
     */
    public $formEventMailText;
    /**
     * @var \Event_Form_File|mixed
     */
    public $formEventFile;
    /**
     * @var Event
     */
    private $event;

    /**
     * @var EventFile
     */
    private $eventFile;

    /**
     * @var Event_Form_File
     */
    private $eventAdminFile;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-file/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->event, 'Event NOT Exists for hash: (' . $this->_getParam('hash') . ') ');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        $eventFileList = Doctrine_Query::create()
            ->from('EventFile ef')
            ->where('ef.id_event = ?', $this->event->getId())
            ->orderBy('ef.title ASC')
            ->execute()
        ;

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Information documents'));
        $this->view->event = $this->event;
        $this->view->eventFileList = $eventFileList;

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'admin_event_edit'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_files',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'event_admin-file'),
        ];
    }

    //dodanie nowego
    public function newAction()
    {
        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->event, 'Event NOT Exists for hash: (' . $this->_getParam('hash') . ') ');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        $this->eventFile = new EventFile();
        $this->eventFile->Event = $this->event;
        $this->eventFile->hash = $this->engineUtils->getHash();

        $this->formEventFile();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New file'));

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_file-new',
            'url' => $this->view->url(),
        ];
    }

    //edycja
    public function editAction()
    {
        //pobranie elementu po polu hash
        $this->eventFile = EventFile::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->eventFile, 'EventFile NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->eventFile->Event),
            [$this->getSelectedBaseUser()->getId(), $this->eventFile->Event->getId()]
        );

        //wyslnie danych do prywatnej funkcji generującej formularz
        $this->formEventFile();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit file'));

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_file-edit',
            'url' => $this->view->url(),
        ];
    }

    //usuwanie
    public function deleteAction()
    {
        //pobranie elementu po polu hash
        $this->eventFile = EventFile::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->eventFile, 'EventFile NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->eventFile->Event),
            [$this->getSelectedBaseUser()->getId(), $this->eventFile->Event->getId()]
        );
        //usuwanie całego obiektu
        $this->event = $this->eventFile->Event;
        $this->eventFile->delete();

        $this->_flash->success->addMessage('Item has been deleted');
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit(['hash' => $this->event->getHash()], 'event_admin-file');
    }

    public function mailTextAction()
    {
        $this->event = Event::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        $this->forward404Unless($this->event, 'Event NOT Exists for hash: (' . $this->_getParam('hash') . ') ');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->event),
            [$this->getSelectedBaseUser()->getId(), $this->event->getId()]
        );

        $this->formEventMailText = new Event_Form_File_MailText($this->event);
        if ($this->_request->isPost() && $this->formEventMailText->isValid($this->_request->getPost())) {
            $this->event->setDownloadMailText($this->formEventMailText->main->getValue('download_mail_text'));
            $this->event->save();
            $this->_flash->success->addMessage('Save successfully completed');
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->event->getHash()], 'event_admin-file');
        }

        $this->view->form = $this->formEventMailText;
        $this->view->placeholder('section-class')->set('tpl-form');
        $this->_helper->viewRenderer('admin/form', null, true);
        $this->view->event = $this->event;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit text'));
    }

    private function formEventFile()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('admin/form', null, true);
        //pobranie formularza dla pojedyńczego elementu
        $this->formEventFile = new Event_Form_File($this->eventFile);

        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->formEventFile->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->eventFileGetRequest();
            //zapis zmian do bazy
            $this->eventFile->save();
            //Tworzenie informacji zwrotnej
            $this->_flash->success->addMessage('Save successfully completed');
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->eventFile->Event->getHash()], 'event_admin-file');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->form = $this->formEventFile;
        $this->view->event = $this->event = $this->eventFile->Event;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'admin_event_edit'),
        ];

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_event_files',
            'url' => $this->view->url(['hash' => $this->event->getHash()], 'event_admin-file'),
        ];
    }

    private function eventFileGetRequest()
    {
        $this->eventFile->setTitle($this->formEventFile->header->getValue('title'));
        $this->eventFile->setLead($this->formEventFile->main->getValue('lead'));
        // zapis pliku
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        if (!empty($files) && isset($files['file']) && 4 !== $files['file']['error']) {
            $this->eventFile->deleteFile();
            $tmp_name = $files['file']['tmp_name'];
            $file_ext = $this->engineUtils->getFileExt($files['file']['name']);
            $this->eventFile->setFileExt($file_ext);
            move_uploaded_file($tmp_name, $this->eventFile->getRelativeFile());
        }

        if (!empty($files) && isset($files['image']) && 4 !== $files['image']['error']) {
            $this->eventFile->deleteImage();

            $tmp_name = $files['image']['tmp_name'];
            $imageInfo = getimagesize($tmp_name);
            $image_ext = $this->engineUtils->getFileExt($files['image']['name']);
            $this->eventFile->setImageExt($image_ext);

            $image = Engine_Image::factory();
            $image->open($tmp_name);
            $image->resizeIfBigger(EventFile::IMAGE_WIDTH, EventFile::IMAGE_HEIGHT);
            $image->save($this->eventFile->getRelativeImage());
        }
    }
}
