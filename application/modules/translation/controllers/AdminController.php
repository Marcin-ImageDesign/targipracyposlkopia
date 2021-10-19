<?php

class Translation_AdminController extends Engine_Controller_Admin
{
    /**
     * @var Translation_Form_Element
     */
    public $form;

    /**
     * @var Translation
     */
    protected $translation;

    /**
     * @var Translation_Form_Element
     */
    protected $formTranslation;

    /**
     * @var Translation_Form_Filter
     */
    protected $filter;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_translation',
            'url' => $this->view->url([], 'admin_translation'),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->_session->translation_list_order = $selectedOrder =
            $this->hasParam('order')
            ? trim($this->getParam('order'))
            : (isset($this->_session->translation_list_order) ? $this->_session->translation_list_order : 'text DESC');

        $this->filter = new Translation_Form_Filter();

        $pager = new Doctrine_Pager($this->filterList(), $this->getParam('page', 1), 100);
        $translationsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->translationsList = $translationsList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;

        $this->view->placeholder('headling_1_content')->set($this->view->translate('List of translations'));
    }

    public function newAction()
    {
        $this->translation = new Translation();
        if (!$this->getSelectedBaseUser()->isSuperAdmin()) {
            $this->translation->BaseUser = $this->getSelectedBaseUser();
        }

        $this->translation->id_language = Engine_I18n::getLangId();
        $this->translation->is_active = true;
        $this->translation->hash = Engine_Utils::_()->getHash();

        $this->formTranslation();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New translation'));

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_translation-new',
            'url' => $this->view->url(),
        ];
    }

    public function editAction()
    {
        $this->translation = Translation::findOneByHash($this->getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->translation, 'Translation NOT Exists (' . $this->getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->translation),
            [$this->getSelectedBaseUser()->getId(), $this->translation->getId()]
        );

        $this->formTranslation();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit translation'));

        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_translation-edit',
            'url' => $this->view->url(),
        ];
    }

    public function deleteAction()
    {
        $this->translation = Translation::findOneByHash($this->getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->translation, 'Translation NOT Exists (' . $this->getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->translation),
            [$this->getSelectedBaseUser()->getId(), $this->translation->getId()]
        );

        $this->translation->delete();

        $this->_flash->succes->addMessage('Translation has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'admin_translation');
    }

    public function statusAction()
    {
        $this->translation = Translation::findOneByHash($this->getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->translation, 'Translation NOT Exists (' . $this->getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->translation),
            [$this->getSelectedBaseUser()->getId(), $this->translation->getId()]
        );

        $this->translation->is_active = !$this->translation->is_active;
        $this->translation->save();

        $this->_flash->succes->addMessage('Translation status has been changed');
        $this->_redirector->gotoRouteAndExit([], 'admin_translation');
    }

    public function importAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Import translations'));
        $this->view->form = new Translation_Form_Upload();
        $this->view->placeholder('section-class')->set('tpl-form');

        if ($this->_request->isPost() && $this->view->form->isValid($this->_request->getPost())) {
            $this->view->form->populate($this->_request->getPost());
            $upload = new Zend_File_Transfer_Adapter_Http();
            $files = $upload->getFileInfo();
            $baseUser = $this->getSelectedBaseUser()->isSuperAdmin() ? null : $this->getSelectedBaseUser();
            if (!empty($files) && isset($files['file']) && 4 !== $files['file']['error']) {
                $handle = fopen($files['file']['tmp_name'], 'r');
                $conn = Doctrine_Manager::connection();

                try {
                    $conn->beginTransaction();
                    $utils = Engine_Utils::getInstance();
                    while (false !== ($data = fgetcsv($handle, 0, ',', '|'))) {
                        if (isset($data[0]) && !empty($data[0]) && isset($data[1]) && !empty($data[1])) {
                            $translation = Translation::findOneByTextAndLanguage($data[0], $this->view->form->header->language->getValue());
                            if (!$translation) {
                                $translation = new Translation();
                                $translation->BaseUser = $baseUser;
                                $translation->hash = $utils->getHash();
                            }
                            $translation->id_language = $this->view->form->header->language->getValue();
                            $translation->is_active = true;
                            $translation->text = $data[0];
                            $translation->translation = $data[1];

                            $translation->save();
                        }
                    }

                    $conn->commit();
                    $this->_flash->succes->addMessage('Translations added from file');
                    $this->_redirector->gotoRouteAndExit([], 'admin_translation');
                } catch (Doctrine_Exception $e) {
                    $conn->rollback();
                    $this->_flash->error->addMessage('Problem while uploading a file');
                    $this->_redirector->gotoRouteAndExit([], 'admin_translation');
                }
            }
        }
        $this->_breadcrumb[] = [
            'label' => 'breadcrumb_cms_translation-import',
            'url' => $this->view->url(),
        ];
    }

    public function uploadAction()
    {
    }

    public function exportAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $fileName = Zend_Registry::get('langvar') . '-' . time();

        header('Content-type: application/csv');
        header("Content-Disposition: attachment;charset=utf-8; filename=\"{$fileName}.csv\"");
        $zTranslate = Zend_Registry::get('Zend_Translate');
        $fp = fopen('php://output', 'w');
        if ($zTranslate instanceof Zend_Translate) {
            $messages = $zTranslate->getAdapter()->getMessages();
            foreach ($messages as $key => $value) {
                fputcsv($fp, [$key, $value], ',', '|');
            }
        }
        fclose($fp);
    }

    public function exportListAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $translationsList = $this->filterList()->execute();

        $fileName = date('Y-m-d_H:i:s');
        header('Content-type: application/csv');
        header("Content-Disposition: attachment;charset=utf-8; filename=\"{$fileName}.csv\"");

        $fp = fopen('php://output', 'w');

        foreach ($translationsList as $key => $value) {
            if ($value instanceof Translation) {
                fputcsv($fp, [$value->getText(), $value->getTranslationText()], ',', '|');
            }
        }

        fclose($fp);
    }

    protected function formTranslation()
    {
        $this->_helper->viewRenderer('form');
        $this->form = new Translation_Form_Element($this->translation);

        if ($this->_request->isPost() && $this->form->isValid($this->_request->getPost())) {
            $this->form->populate($this->_request->getPost());
            $this->getTranslationFromRequest();
            $this->translation->save();
            $this->_flash->succes->addMessage($this->view->translate('Save successfully completed'));
            $this->_redirector->gotoRouteAndExit(['hash' => $this->translation->getHash()], 'admin_translation_edit');
        }

        $this->view->form = $this->form;
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    protected function getTranslationFromRequest()
    {
        $this->translation->setText($this->form->main->text->getValue());
        $this->translation->setTranslationText($this->form->main->translation->getValue());
        $this->translation->setIsActive($this->form->main->is_active->getValue());
        $this->translation->setLanguageId($this->form->main->language->getValue());
        $this->translation->setModifiedAt(date('Y-m-d H:i:s'));
    }

    private function filterList()
    {
        $search = ['text' => '', 'translation' => '', 'is_active' => '', 'language' => '', 'date_start' => '', 'date_end' => ''];

        if ($this->hasParam('clear')) {
            $this->_session->__unset('translation_list_filter');
        } else {
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->translation_list_filter->{$key} = $this->hasParam($key)
                    ? trim($this->getParam($key, $value))
                    : (isset($this->_session->translation_list_filter->{$key})
                        ? $this->_session->translation_list_filter->{$key} : $value);
            }
        }

        if (isset($this->filter)) {
            $this->filter->populate($search);
        }

        $translationsListQuery = Doctrine_Query::create()
            ->from('Translation t');

        if (!$this->getSelectedBaseUser()->isSuperAdmin()) {
            $translationsListQuery->addWhere('t.id_base_user = ?', $this->getSelectedBaseUser()->getId());
        }

        if (!empty($search['text'])) {
            $translationsListQuery->addWhere('t.text Like ?', '%' . $search['text'] . '%');
        }

        if (!empty($search['translation'])) {
            $translationsListQuery->addWhere('t.translation LIKE ?', '%' . $search['translation'] . '%');
        }

        if (!empty($search['language'])) {
            $translationsListQuery->addWhere('t.id_language = ?', $search['language']);
        }

        if (!empty($search['date_start'])) {
            $translationsListQuery->addWhere('t.modified_at >= ?', $search['date_start'] . ' 00:00:00');
        }

        if (!empty($search['date_end'])) {
            $translationsListQuery->addWhere('t.modified_at <= ?', $search['date_end'] . ' 23:59:59');
        }

        if (!empty($search['is_active'])) {
            $translationsListQuery->addWhere('t.is_active = ?', $search['is_active']);
        }

        return $translationsListQuery;
    }
}
