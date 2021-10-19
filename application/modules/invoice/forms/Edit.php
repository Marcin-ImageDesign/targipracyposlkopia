<?php

class Invoice_Form_Edit extends Invoice_Form_Invoice
{
    public function init()
    {
        parent::init();

        $this->id_exhib_participation->setIgnore(true);
        $this->id_exhib_participation->setAllowEmpty(true);
    }
}
