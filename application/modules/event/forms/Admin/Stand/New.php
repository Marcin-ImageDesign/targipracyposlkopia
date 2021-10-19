<?php

class Event_Form_Admin_Stand_New extends Event_Form_Admin_Stand
{
    /**
     * @param ExhibStand $exhibStand
     * @param array      $options
     * @param mixed      $language
     */
    public function __construct($exhibStand, $language, $options = null)
    {
        $this->_exhibStand = $exhibStand;
        $this->_language = $language;
        parent::__construct($options);
    }
}
