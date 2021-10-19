<?php

class News_Form_Admin_News_Edit extends News_Form_Admin_News
{
    /**
     * @param News       $news
     * @param array      $options
     * @param null|mixed $eventId
     */
    public function __construct($news, $eventId = null, $options = null)
    {
        $this->_news = $news;
        $this->_eventId = $eventId;
        parent::__construct($options);
    }
}
