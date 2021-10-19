<?php

interface Briefcase_Service_Interface
{
    /**
     * @param int $element
     *
     * @return string
     */
    public function getElement($element);

    public function getView($elementsIds);
}
