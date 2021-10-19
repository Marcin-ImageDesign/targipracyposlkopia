<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of New.
 *
 * @author Marek Skotarek
 */
class Event_Form_StandFile_New extends Event_Form_StandFile
{
    /**
     * @param Stand $exhib_stand
     * @param array $options
     * @param mixed $exhib_stand_file
     */
    public function __construct($exhib_stand_file, $options = null)
    {
        parent::__construct($exhib_stand_file, $options);
    }
}
