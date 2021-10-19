<?php

class Invoice_AdminController extends Engine_Controller_Admin
{
    /**
     * @var mixed[]|mixed|null
     */
    public $file;
    protected $_model;

    private $invoice;
    private $invoiceList;
    private $form;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => 'invoice_cms_brand',
            'url' => $this->view->url([], 'brand_admin'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Lista faktur'));

        $invoiceList = Doctrine_Query::create()
            ->from('Invoice i')
            ->execute()
        ;

        $this->view->invoiceList = $invoiceList;
    }

    public function newAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Dodaj fakturę'));
        $engineUtils = Engine_Utils::_();

        $this->invoice = new Invoice();

        $form = $this->form = new Invoice_Form_Invoice([
            'invoice' => $this->invoice,
        ]);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $this->invoiceFileGetRequest();
            $formValues = $form->getValues();
            $this->invoice->id_exhib_participation = $formValues['id_exhib_participation'];
            $this->invoice->hash = $engineUtils->getHash();
            $this->invoice->number = $formValues['number'];
            $this->invoice->price_net = $formValues['price_net'];
            $this->invoice->price_gross = $formValues['price_gross'];
            $this->invoice->price_vat = $formValues['price_vat'];
            $this->invoice->title = $formValues['title'];
            $this->invoice->status_id = $formValues['status'];
            $this->invoice->date_created = date('Y-m-d');
            //zapis
            $this->invoice->save();
            //zapis pliku z id
            $this->saveFile();
            $this->_flash->succes->addMessage($this->view->translate('Faktura dodana pomyślnie'));
            $this->_redirector->gotoRouteAndExit([], 'invoice_admin-index');
        }

        $this->view->form = $form;
    }

    public function editAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edytuj fakturę'));

        $this->invoice = Invoice::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->invoice, 'Invoice Not Found hash: (' . $this->_getParam('hash') . ')');

        $form = $this->form = new Invoice_Form_Edit([
            'invoice' => $this->invoice,
        ]);
        $hash = $this->invoice->hash;
        $this->view->hash = $hash;

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $this->invoiceFileGetRequest();
                $formValues = $form->getValues();
                $this->invoice->number = $formValues['number'];
                $this->invoice->price_net = $formValues['price_net'];
                $this->invoice->price_gross = $formValues['price_gross'];
                $this->invoice->price_vat = $formValues['price_vat'];
                $this->invoice->title = $formValues['title'];
                $this->invoice->status_id = $formValues['status'];

                $this->invoice->save();
                $this->saveFile();
            }
            $this->_flash->succes->addMessage($this->view->translate('cms_message_save_success'));
            $this->_redirector->gotoRouteAndExit(['hash' => $this->invoice->hash], 'invoice_admin-edit');
        }
        $this->view->form = $form;
    }

    public function detailsAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Szczegóły faktury'));

        $this->invoice = Invoice::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->invoice, 'Invoice Not Found hash: (' . $this->_getParam('hash') . ')');

        $form = $this->form = new Invoice_Form_Details([
            'invoice' => $this->invoice,
        ]);

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $this->invoice = Invoice::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->invoice, 'Invoice Not Found hash: (' . $this->_getParam('hash') . ')');

        $this->invoice->delete();
        $this->_flash->succes->addMessage($this->view->translate('cms_message_delete_success'));
        $this->_redirector->gotoRouteAndExit([], 'invoice_admin-index');
    }

    public function downloadAction()
    {
        $this->invoice = Invoice::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->invoice, 'Invoice Not Found hash: (' . $this->_getParam('hash') . ')');

        $filename = $this->invoice->getFileName();
        $file_path = $this->invoice->getRelativeFile();

        if (!empty($file_path) && is_file($file_path)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($file_path);
        } else {
            echo 'The specified file does not exist';
        }
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function deleteFileAction()
    {
        //pobranie elementu po polu hash
        $this->invoice = Invoice::findOneByHash($this->_getParam('hash'));
        //usuwanie pliku
        $this->invoice->deleteFile();
        $this->invoice->file_name = '';
        $this->invoice->save();
        $this->_flash->success->addMessage('Plik został usunięty');
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit(['hash' => $this->invoice->hash], 'invoice_admin-edit');
    }

    private function invoiceFileGetRequest()
    {
        $upload = new Zend_File_Transfer_Adapter_Http();
        $this->file = $upload->getFileInfo();

        if (!empty($this->file) && isset($this->file['file_name']) && 4 !== $this->file['file_name']['error']) {
            $this->invoice->file_name = $this->form->file_name->getValue();
            $this->invoice->file_ext = $this->engineUtils->getFileExt($this->file['file_name']['name']);
        } else {
            $this->file = null;
        }
    }

    private function saveFile()
    {
        if (!empty($this->file) && !empty($this->invoice)) {
            $this->file = $this->form->file_name->getTransferAdapter()->getFileInfo();
            $tmp_name = $this->file['file_name']['tmp_name'];
            if (is_file($tmp_name)) {
                return rename($tmp_name, $this->invoice->getRelativeFile());
            }
        }
    }
}
