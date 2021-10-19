<?php

class Menu_AdminController extends Engine_Controller_Admin
{
    public $renderLeftCol;
    /**
     * @var \Doctrine_Query|mixed
     */
    public $menuListQuery;
    /**
     * @var Menu
     */
    private $menu;

    /**
     * @var Menu
     */
    private $menuParent;

    /**
     * @var Form_Menu
     */
    private $formMenu;

    /**
     * @var Doctrine_Collection
     */
    private $menuList;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_menu'),
            'url' => $this->view->url([], 'admin_menu'),
        ];
    }

    public function postDispatch()
    {
        $this->renderLeftCol[] = 'admin/_contentNav.phtml';

        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->menuListQuery = Doctrine_Query::create()
            ->from('Menu m')
            ->leftJoin('m.Menu m1 WITH m1.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->leftJoin('m1.Menu m2 WITH m2.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->where('m.id_menu_parent IS NULL AND m.id_base_user = ?', $this->getSelectedBaseUser()->getId())
        ;

        if (!$this->getSelectedBaseUser()->isDefaultLanguage($this->getSelectedLanguage()) && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
            $this->menuListQuery->leftJoin('m.MenuLanguageOne mlo WITH mlo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
            $this->menuListQuery->leftJoin('m1.MenuLanguageOne mlo1 WITH mlo1.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
            $this->menuListQuery->leftJoin('m2.MenuLanguageOne mlo2 WITH mlo2.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
        }

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Menu settings'));
        $this->view->menuList = $this->menuListQuery->execute();
    }

    public function newAction()
    {
        $hash_parent = $this->_getParam('hash_parent');
        if ($hash_parent) {
            $this->menuParent = Menu::findOneByHashAndIdBaseUser($this->_getParam('hash_parent'), $this->getSelectedBaseUser());
        }

        $this->menu = new Menu();
        $this->menu->BaseUser = $this->getSelectedBaseUser();
        $this->menu->hash = $this->engineUtils->getHash();
        $this->menu->is_active = true;
        $this->menu->id_menu_type = Menu::MENU_TYPE_TEXT;
        if ($this->menuParent) {
            $this->menu->MenuParent = $this->menuParent;
        }
        $this->menu->order = $this->menu->getLastOrder() + 1;

        $this->setSelectedLanguage();

        $this->formMenu();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Add page menu'));

        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_menu_new'),
            'url' => $this->view->url(),
        ];
    }

    public function editAction()
    {
        $this->menu = Menu::findOneByHash($this->_getParam('hash'), $this->getSelectedBaseUser(), $this->getSelectedLanguage());
        $this->forward404Unless($this->menu, 'Menu NOT Exists (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->menu),
            [$this->getSelectedBaseUser()->getId(), $this->menu->getId()]
        );

        $this->menu->refreshRelated('MenuParent');
        $this->formMenu();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit page'));

        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_menu_edit'),
            'url' => $this->view->url(),
        ];
    }

    public function deleteAction()
    {
        $this->menu = Menu::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->menu, 'Menu NOT Exists (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->menu),
            [$this->getSelectedBaseUser()->getId(), $this->menu->getId()]
        );
        $this->menu->refreshRelated('Menu');

        if ($this->menu->Menu->count() > 0) {
            $this->_flash->addMessage(['msg-error', $this->view->translate('To delete an item, you must delete all pairings assigned to positions')]);
        } else {
            $this->menu->delete();
            $this->_flash->addMessage(['msg-ok', $this->view->translate('Item has been deleted')]);
        }

        $this->_redirector->gotoRouteAndExit([], 'admin_menu');
    }

    public function statusAction()
    {
        $this->menu = Menu::findOneByHash($this->_getParam('hash'));
        $this->forward404Unless($this->menu, 'Menu NOT Exists (' . $this->_getParam('hash') . ')');
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->menu),
            [$this->getSelectedBaseUser()->getId(), $this->menu->getId()]
        );

        $this->menu->is_active = !$this->menu->is_active;
        $this->menu->save();

        $this->_redirector->gotoRouteAndExit([], 'admin_menu');
    }

    /**
     * @todo zapis uri_full dla przypusanych grupowo
     */
    public function moveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // pobranie przenoszonego elementu
        $menuMove = Menu::find($this->_getParam('id_menu_move'), $this->getSelectedLanguage());
        $this->forward404Unless($menuMove, 'Menu NOT Exists hash: (' . $this->_getParam('id_menu_move') . ')');
        $this->forward403Unless($this->getSelectedBaseUser()->hasAccess($menuMove), [$this->getSelectedBaseUser()->getId(), $menuMove->getId()]);

        $menu = Menu::find($this->_getParam('id_menu'), $this->getSelectedLanguage());
        $this->forward404Unless($menu, 'Menu NOT Exists hash (' . $this->_getParam('hash') . ')');
        $this->forward403Unless($this->getSelectedBaseUser()->hasAccess($menu), [$this->getSelectedBaseUser()->getId(), $menu->getId()]);

        $typeMove = $this->_getParam('type');
        if (!in_array($typeMove, ['inside', 'before', 'after'], true)) {
            $this->forward404('Type Unknown (' . $typeMove . ') to move element');
        }

        // przygotowanie do updatu zmiany kolejności ketegori w której znajduje/znajdował
        // się przenoszony element menu
        $categoryMoveUpdate = Doctrine_Query::create()
            ->update('Menu m')
            ->where('m.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->addWhere('m.id_menu != ?', $menuMove->getId())
        ;

        if (null === $menuMove->id_menu_parent) {
            $categoryMoveUpdate->addWhere('m.id_menu_parent IS NULL');
        } else {
            $categoryMoveUpdate->addWhere('m.id_menu_parent = ?', $menuMove->id_menu_parent);
        }

        $orderDiff = null;
        $menu_move_new_order = null;
        $categoryChangeUpdate = null;
        // obliczanie różnicy, jeśli przenosimy element w tej samej kategorii
        // niezbędny do obliczenia w którą strone przenosimy i wykonania odpowiedniego
        // updatu
        if ('inside' !== $typeMove && $menuMove->id_menu_parent === $menu->id_menu_parent) {
            $orderDiff = $menuMove->order - $menu->order;

            // przenosimy w dół
            if ($orderDiff > 0) {
                $categoryMoveUpdate->set('order', 'order + 1');
                $categoryMoveUpdate->addWhere('m.order < ?', [$menuMove->order]);
                if ('after' === $typeMove) {
                    $categoryMoveUpdate->addWhere('m.order >= ?', [$menu->order]);
                    $menu_move_new_order = $menu->order;
                } elseif ('before' === $typeMove) {
                    $categoryMoveUpdate->addWhere('m.order > ?', [$menu->order]);
                    $menu_move_new_order = $menu->order + 1;
                }
                //przenosimy do góry
            } elseif ($orderDiff < 0) {
                $categoryMoveUpdate->set('order', 'order - 1');
                $categoryMoveUpdate->addWhere('m.order > ?', [$menuMove->order]);
                if ('after' === $typeMove) {
                    $categoryMoveUpdate->addWhere('m.order < ?', [$menu->order]);
                    $menu_move_new_order = $menu->order - 1;
                } elseif ('before' === $typeMove) {
                    $categoryMoveUpdate->addWhere('m.order <= ?', [$menu->order]);
                    $menu_move_new_order = $menu->order;
                }
            } else {
                throw new Exception('OrderDiff IS 0');
            }

            // przypisujemy kolejność dla przenoszonego elementu
            if (null === $menu_move_new_order) {
                throw new Exception('New Order Menu IS NULL');
            }

            // jeśli przenosimy do środka,
            // przenieść można tylko jeden, jako pierwszy element jeśli zawartość jest pusta
            // jeśli już instnieją elementy w elemencie do którego przenisimy element
            // typ; przenoszenia jest zawsze after pierwszy
        } elseif ('inside' === $typeMove) {
            $menuMove->id_menu_parent = $menu->getId();
            $categoryMoveUpdate->set('order', 'order - 1');
            $categoryMoveUpdate->addWhere('m.order > ?', [$menuMove->order]);
            $menu_move_new_order = 1;
        } elseif ($menuMove->id_menu_parent !== $menu->id_menu_parent) {
            $menuMove->id_menu_parent = $menu->id_menu_parent;
            $categoryMoveUpdate->set('order', 'order - 1');
            $categoryMoveUpdate->addWhere('m.order > ?', [$menuMove->order]);

            // zapytanie zmieniające kolejność elementów wewnątrz nowej grupy
            $categoryChangeUpdate = Doctrine_Query::create()
                ->update('Menu m')
                ->where('m.id_base_user = ?', $this->getSelectedBaseUser()->getId())
                ->addWhere('m.id_menu != ?', $menuMove->getId())
                ->set('order', 'order + 1')
            ;

            if (null === $menu->id_menu_parent) {
                $categoryChangeUpdate->addWhere('m.id_menu_parent IS NULL');
                $menuParent = false;
            } else {
                $categoryChangeUpdate->addWhere('m.id_menu_parent = ?', $menu->id_menu_parent);
                $menuParent = $menu->MenuParent;
            }

            if ('after' === $typeMove) {
                $categoryChangeUpdate->addWhere('m.order >= ?', [$menu->order]);
                $menu_move_new_order = $menu->order;
            } elseif ('before' === $typeMove) {
                $categoryChangeUpdate->addWhere('m.order > ?', [$menu->order]);
                $menu_move_new_order = $menu->order + 1;
            }
        } else {
            throw new Exception('Unknown problem');
        }

        // zapis zamin
        $conn = Doctrine_Manager::connection();

        try {
            $conn->beginTransaction();

            $categoryMoveUpdate->execute();
            if (null !== $categoryChangeUpdate) {
                $categoryChangeUpdate->execute();
            }
            $menuMove->order = $menu_move_new_order;
            $menuMove->setUriFull($menuMove->getUriFull(true));
            $menuMove->save();
            $menuMove->recreateChildUriFull($this->getSelectedLanguage());
            $menuMove->save();

            $conn->commit();
        } catch (Doctrine_Exception $e) {
            $conn->rollback();

            throw new Exception('Database save changed order problem:' . $e, $e->getCode(), $e);
        }
    }

    private function formMenu()
    {
        $this->_helper->viewRenderer('form-menu');
        $this->formMenu = $this->getFormMenu();
//        $pluginClass = $this->initCurrentPlugin($this->menu, $this->formMenu, ElementType::TYPE_MENU);

        if ($this->_request->isPost() && $this->formMenu->isValid($this->_request->getPost())) {
            $this->menuGetRequest();
            $this->menu->setUriFull($this->menu->getUriFull(true));
            $this->menu->save();
            $this->menu->recreateChildUriFull($this->getSelectedLanguage());
            $this->menu->save();
            //                $this->runPluginMethod($pluginClass, 'postSave');
            $this->_flash->addMessage(['msg-ok', $this->view->translate('Save successfully completed')]);
            $this->_redirector->gotoRouteAndExit(['hash' => $this->menu->hash], 'admin_menu_edit');
        }

        $this->view->formMenu = $this->formMenu;
        $this->view->menu = $this->menu;
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function menuGetRequest()
    {
        if (!$this->getSelectedBaseUser()->isDefaultLanguage($this->getSelectedLanguage()) && !isset($this->menu->MenuLanguageOne)) {
            $this->menu->MenuLanguageOne = new MenuLanguage();
            $this->menu->MenuLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }

        $this->menu->is_header = (bool) $this->formMenu->main->is_header->getValue();
        $this->menu->is_footer = (bool) $this->formMenu->main->is_footer->getValue();
        $this->menu->id_menu_type = $this->formMenu->main->id_menu_type->getValue();
        $this->menu->is_metatag = (bool) $this->formMenu->seo->is_metatag->getValue();
        $this->menu->setTitle($this->formMenu->header->title->getValue());
        $this->menu->setUri($this->engineUtils->getFriendlyUri($this->menu->getTitle()));
        $this->menu->setUriFull($this->menu->getUriFull(true));

        if ($this->menu->isMenuTypeLink()) {
            $this->menu->setLink($this->formMenu->main->link->getValue());
        } elseif ($this->menu->isMenuTypeText()) {
            $this->menu->setText($this->formMenu->main->text->getValue());
        }

        if ($this->menu->is_metatag) {
            $this->menu->setMetatagTitle($this->formMenu->seo->metatag_title->getValue());
            $this->menu->setMetatagKey($this->formMenu->seo->metatag_key->getValue());
            $this->menu->setMetatagDesc($this->formMenu->seo->metatag_desc->getValue());
        }
    }

    /**
     * @return Admin_Form_Menu
     */
    private function getFormMenu()
    {
        $formMenu = new Admin_Form_Menu($this->menu);
        $formMenu->populate($this->menu->getArrayToForm());

        if (!$this->getSelectedBaseUser()->isDefaultLanguage($this->getSelectedLanguage()) && !isset($this->menu->MenuLanguageOne)) {
            $this->menu->MenuLanguageOne = new MenuLanguage();
            $this->menu->MenuLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }

        // jakie są roz.
        // wykonaj w pętki

        $params = ['uriFilter' => true];
        if (!$this->menu->isNew()) {
            $params[] = ['id_menu', '!=', $this->menu->getId()];
        }

        if (isset($this->menu->MenuLanguageOne)) {
            $model = 'MenuLanguage';
            $params[] = ['id_base_user_language', '=', $this->menu->MenuLanguageOne->id_base_user_language];
        } else {
            $model = 'Menu';
            $params[] = ['id_base_user', '=', $this->getSelectedBaseUser()->getId()];
        }

        $vAlreadyTaken = new Engine_Validate_AlreadyTaken($model, 'uri_full', $params);
        $formMenu->getSubForm('header')->getElement('title')->addValidator($vAlreadyTaken);

        return $formMenu;
    }
}
