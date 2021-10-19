<?php

class News_Form_Admin_News_New extends News_Form_Admin_News
{
    /**
     * @param News  $news
     * @param array $options
     * @param mixed $eventId
     */
    public function __construct($news, $eventId, $options = null)
    {
        $this->_news = $news;
        $this->_eventId = $eventId;
        parent::__construct($options);
    }
}
