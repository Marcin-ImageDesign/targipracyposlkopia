<?php

class Event_Form_Admin_StandProduct_Edit extends Event_Form_Admin_StandProduct
{
    /**
     * @param StandProduct $standProduct
     * @param array        $options
     */
    public function __construct($standProduct, $options = null)
    {
        $this->_standProduct = $standProduct;
        parent::__construct($options);
    }
}
