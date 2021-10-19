<?php

class Invoice_Form_Invoice extends Engine_FormAdmin
{
    // protected $_belong_to = 'InvoiceFormElement';
    protected $_tlabel = 'form_invoice-admin-element_';
    protected $invoice;

    public function init()
    {
        $this->addExhibitors();
        $this->addMainGroup();
        $this->addSubmitBtn();
    }

    public function addMainGroup()
    {
        $mainFields = null;
        $mainFields['number'] = $this->createElement('text', 'number', [
            'label' => $this->_tlabel . 'number',
            'filters' => ['StringTrim'],
            'required' => true,
            'value' => $this->invoice->getNumber(),
        ]);

        $mainFields['price_net'] = $this->createElement('text', 'price_net', [
            'label' => $this->_tlabel . 'price_net',
            'filters' => ['StringTrim'],
            'required' => true,
            'value' => $this->invoice->getPriceNet(),
        ]);

        $mainFields['price_vat'] = $this->createElement('text', 'price_vat', [
            'label' => $this->_tlabel . 'price_vat',
            'filters' => ['StringTrim'],
            'required' => true,
            'value' => $this->invoice->getPriceVat(),
        ]);

        $mainFields['price_gross'] = $this->createElement('text', 'price_gross', [
            'label' => $this->_tlabel . 'price_gross',
            'filters' => ['StringTrim'],
            'required' => true,
            'value' => $this->invoice->getPriceGross(),
        ]);

        $mainFields['title'] = $this->createElement('text', 'title', [
            'label' => $this->_tlabel . 'title',
            'filters' => ['StringTrim'],
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'required' => true,
            'value' => $this->invoice->getTitle(),
        ]);

        $listOptions = ['1' => 'label_form_to_pay', '2' => 'label_form_paid', '3' => 'label_form_cancelled'];

        $mainFields['status'] = $this->createElement('select', 'status', [
            'label' => $this->_tlabel . 'status',
            'multiOptions' => $listOptions,
            'value' => $this->invoice->getStatusId(),
            'required' => true,
            'allowEmpty' => false,
        ]);

        $mainFields['file_name'] = $this->createElement('file', 'file_name', [
            'label' => $this->_tlabel . 'file_pdf',
            'allowEmpty' => true,
            'class' => 'field-file',
            'size' => 50,
        ]);
        $mainFields['file_name']->addValidator('Count', false, 1);
        $mainFields['file_name']->addValidator('Size', false, MAX_FILE_SIZE);

        $mainFields['file_name']->setDecorators($this->elementDecoratorsCenturionFile);
        if ($this->invoice->getFileName()) {
            $fileShowDecorator = $mainFields['file_name']->getDecorator('FileShow');
            $fileShowDecorator->setOptions([
                'title' => 'Pobierz plik ' . $this->invoice->getFileName(),
                'file' => $this->getView()->url(['hash' => $this->invoice->getHash()], 'invoice_admin-download'),
            ]);
        }

        $this->addDisplayGroup(
            $mainFields,
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'invoice_info',
            ]
        );
    }

    public function addExhibitors()
    {
        $roleFields = null;
        $exhibitors = Doctrine_Query::create()
            ->from('ExhibParticipation p')
            ->leftJoin('p.User u')
            ->where('p.id_exhib_participation_type = ?', 2)
            ->execute()
        ;

        foreach ($exhibitors as $exhibitor) {
            $exhibitorsList[$exhibitor->getId()] = $exhibitor->User->getName();
        }

        $roleFields['id_exhib_participation'] = $this->createElement('select', 'id_exhib_participation', [
            'multiOptions' => $exhibitorsList,
            'allowEmpty' => false,
            'value' => $this->invoice->id_exhib_participation,
        ]);

        $this->addDisplayGroup(
            $roleFields,
            'main-role',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'invoice_exhibitors',
            ]
        );
    }

    public function addSubmitBtn()
    {
        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'group-wrapper');
    }

    protected function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }
}
