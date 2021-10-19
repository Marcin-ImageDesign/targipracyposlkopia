<?php

class Text_AdminController extends Engine_Controller_Admin
{
    /**
     * @var Text
     */
    private $_text;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        if ($this->hasParam('hash')) {
            $this->_text = Text::findOneByHash($this->getParam('hash'));
        }

        $textQuery = Doctrine_Query::create()->from('Text t')->leftJoin('t.Translations');
        if ($this->_text) {
            $textQuery->addWhere('t.id_text_parent = ?', $this->_text->getId());
        } else {
            $textQuery->addWhere('t.id_text_parent IS NULL');
        }

        $this->view->textList = $this->_helper->paging($textQuery);
        $this->view->text = $this->_text;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_admin_text-list'));
    }

    public function newAction()
    {
        $this->_text = new Text();
        $this->_text->hash = Engine_Utils::_()->getHash();

        if ($this->hasParam('hash')) {
            $textParent = Text::findOneByHash($this->getParam('hash'));
            if ($textParent) {
                $this->_text->Parent = $textParent;
            }
        }

        $this->_formText();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-h1_text_new'));
    }

    public function editAction()
    {
        $this->_text = Text::findOneByHash($this->getParam('hash'));
        $this->forward404Unless($this->_text);

        $this->_formText();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('cms-h1_text_edit'));
    }

    public function statusAction()
    {
        $this->_text = Text::findOneByHash($this->getParam('hash'));
        $this->forward404Unless($this->_text);

        $this->_text->setIsActive(!$this->_text->getIsActive());
        $this->_text->save();

        $params = [];
        if ($this->_text->id_text_parent) {
            $params = ['hash' => $this->_text->Parent->getHash()];
        }

        $url = $this->view->url($params, 'text_admin') . '?page=' . $this->getParam('page', 1);
        $this->_redirector->gotoUrlAndExit($url);
    }

    public function deleteAction()
    {
        $this->_text = Text::findOneByHash($this->getParam('hash'));
        $this->forward404Unless($this->_text);

        $this->_text->delete();

        $params = [];
        if ($this->_text->id_text_parent) {
            $params = ['hash' => $this->_text->Parent->getHash()];
        }

        $url = $this->view->url($params, 'text_admin') . '?page=' . $this->getParam('page', 1);

        $this->_flash->succes->addMessage($this->view->translate('cms-msg_remove-done'));
        $this->_redirector->gotoUrlAndExit($url);
    }

    private function _formText()
    {
        $form = new Text_Form_Text(['model' => $this->_text]);
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $this->_text->save();

            $this->_flash->succes->addMessage($this->view->translate('cms-msg_save-done'));
            $params = [];
            if ($this->_text->relatedExists('Parent')) {
                $params = ['hash' => $this->_text->Parent->getHash()];
            }

            $this->_redirector->gotoRouteAndExit($params, 'text_admin');
        }

        $this->view->form = $form;
        $this->_helper->viewRenderer('form');
    }
}
