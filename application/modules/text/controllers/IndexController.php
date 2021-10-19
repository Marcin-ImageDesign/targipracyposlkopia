<?php

class Text_IndexController extends Engine_Controller_Frontend
{
    /**
     * @var Text
     */
    private $_text;

    public function indexAction()
    {
        $this->_text = Text::findOneByUri($this->getParam('uri'));
        $this->forward404Unless($this->_text);

        $meta_title = $this->_text->getMetaTitle();
        $meta_keywords = $this->_text->getMetaKeywords();
        $meta_description = $this->_text->getMetaDescription();
        if (!empty($meta_title)) {
            $this->view->headTitle($meta_title);
        }
        if (!empty($meta_keywords)) {
            $this->view->headMeta()->setName('keywords', $meta_keywords);
        }
        if (!empty($meta_description)) {
            $this->view->headMeta()->setName('description', $meta_description);
        }

        $this->view->text = $this->_text;
    }
}
