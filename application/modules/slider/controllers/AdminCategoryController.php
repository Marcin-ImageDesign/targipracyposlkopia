<?php

class Slider_AdminCategoryController extends Engine_Controller_Admin
{
    /**
     * @var \Slider_Form_Category_Filter|mixed
     */
    public $filter;
    /**
     * @var \Doctrine_Query|mixed
     */
    public $sliderCategoryListQuery;
    /**
     * @var SliderCategory
     */
    private $sliderCategory;

    /**
     * @var Slider_Form_Category_Element
     */
    private $formSliderCategory;

    /**
     * @var Doctrine_Collection
     */
    private $sliderCategoryList;

    private $is_category;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->is_category = 1;
        if ($this->is_category === 0) {
            $this->forward404('Category is disabled');
        }
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu slider category
            $this->view->renderToPlaceholder('admin-category/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        // Ustawienie sortowania listy
        $selectedOrder = $this->_session->slider_category_order = $this->_hasParam('order') ? trim($this->_getParam('order', 'created_at DESC')) : (isset($this->_session->slider_category_order) ? $this->_session->slider_category_order : 'created_at DESC');
        //Deklaracja tablicy z filtrami
        $search = ['title' => '', 'is_active' => ''];

        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('slider_category_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->slider_category_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->slider_category_filter->{$key}) ? $this->_session->slider_category_filter->{$key} : $value);
            }
        }
        // deklaracja formularza slider/form/category/Filter.php
        $this->filter = new Slider_Form_Category_Filter();
        $this->filter->populate($search);

        //tworzenie zaptania zwracającego listę
        $this->sliderCategoryListQuery = Doctrine_Query::create()
            ->from('SliderCategory sc')
            ->where('sc.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->orderBy('sc.' . $selectedOrder)
        ;

        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
            $this->sliderCategoryListQuery->leftJoin('sc.SliderCategoryLanguageOne sclo WITH sclo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
        }
        //sprawdzenie tytułu z filtra
        if (!empty($search['title'])) {
            $this->sliderCategoryListQuery->addWhere('sc.title LIKE ?', '%' . $search['title'] . '%');
        }
        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $this->sliderCategoryListQuery->addWhere('sc.is_active = ?', $search['is_active']);
        }

        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
        $pager = new Doctrine_Pager($this->sliderCategoryListQuery, $this->_getParam('page', 1), 20);
        $sliderCategoryList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->sliderCategoryList = $sliderCategoryList;
        $this->view->selectedOrder = $selectedOrder;
        $this->view->orderCol = trim(mb_substr($selectedOrder, 0, -4));
        $this->view->orderDir = trim(mb_substr($selectedOrder, -4));
        $this->view->filter = $this->filter;

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set('Slider Category');
    }

    //Zmiana statusu (aktywny/nie aktywny) na podstronie listy.
    public function statusAction()
    {
        //pobranie elemntu po polu hash
        $this->sliderCategory = SliderCategory::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy dany element istnieje
        $this->forward404Unless($this->sliderCategory, 'Slider category NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->sliderCategory),
            [$this->getSelectedBaseUser()->getId(), $this->sliderCategory->getId()]
        );

        //zmiana statusu
        $this->sliderCategory->is_active = !(bool) $this->sliderCategory->is_active;
        //zapis do bazy
        $this->sliderCategory->save();
        //przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'admin_slider-category');
    }

    //dodanie nowego elementu
    public function newAction()
    {
        //tworzenie nowej instancji
        $engineUtils = Engine_Utils::getInstance();
        //tworzenie nowego elementu
        $this->sliderCategory = new SliderCategory();
        //pobieranie id klienta
        $this->sliderCategory->BaseUser = $this->getSelectedBaseUser();

        $this->setSelectedLanguage();
        //pobranie tymczasowego hash-a
        $this->sliderCategory->hash = $engineUtils->getHash();

        //uruchomienie prywatnej funkcji generującej formularz
        $this->formSliderCategory();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New category'));
    }

    //edycja istniejącego elementu
    public function editAction()
    {
        //pobranie elementu po polu hash
        $this->sliderCategory = SliderCategory::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->sliderCategory, 'Slider category NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->sliderCategory),
            [$this->getSelectedBaseUser()->getId(), $this->sliderCategory->getId()]
        );

        //uruchomienie prywatnej funkcji generującej formularz
        $this->formSliderCategory();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Edit category'));
    }

    //usuwanie slidera
    public function deleteAction()
    {
        //pobranie obiektu po polu hash
        $this->sliderCategory = SliderCategory::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->sliderCategory, 'Slider category NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->sliderCategory),
            [$this->getSelectedBaseUser()->getId(), $this->sliderCategory->getId()]
        );
        //usuwanie całego obiektu
        $this->sliderCategory->delete();
        $this->_flash->addMessage(['msg-ok', 'Slider category został usunięty']);
        //Przekierowanie na podstronę listy
        $this->_redirector->gotoRouteAndExit([], 'admin_slider-category');
    }

    private function formSliderCategory()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('form-slider-category');
        //pobranie formularza dla pojedyńczego elementu
        $this->formSliderCategory = $this->getFormSliderCategory();

        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->formSliderCategory->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->sliderCategoryGetRequest();
            //zapis zmian do bazy
            $this->sliderCategory->save();
            //Tworzenie informacji zwrotnej
            $this->_flash->addMessage(['msg-ok', 'Slider category został zapisany']);
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->sliderCategory->hash], 'admin_slider-category_edit');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->formSliderCategory = $this->formSliderCategory;
        $this->view->sliderCategory = $this->sliderCategory;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    private function sliderCategoryGetRequest()
    {
        $thsis = null;
        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && !isset($this->sliderCategory->SliderCategoryLanguageOne)) {
            $this->sliderCategory->SliderCategoryLanguageOne = new SliderCategoryLanguage();
            $this->sliderCategory->SliderCategoryLanguageOne->BaseUserLanguage = $thsis->getSelectedLanguage()->BaseUserLanguageOne;
        }

        //Przypisanie pol z formularza do elemntów obiektu slider category
        $this->sliderCategory->is_active = (bool) $this->formSliderCategory->aside->is_active->getValue();
        $this->sliderCategory->setTitle($this->formSliderCategory->header->title->getValue());
        $this->sliderCategory->setWidth($this->formSliderCategory->main->width->getValue());
        $this->sliderCategory->setHeight($this->formSliderCategory->main->height->getValue());
        $this->sliderCategory->setUri($this->formSliderCategory->main->uri->getValue());
    }

    /**
     * @return Slider_Form_Category_Element
     */
    private function getFormSliderCategory()
    {
        //Pobranie formularza slider/from/category/Element.php
        $formSliderCategory = new Slider_Form_Category_Element($this->sliderCategory);
        //Tworzenie tablicy z elemntów formularza
        $formSliderCategory->populate($this->sliderCategory->getArrayToForm());
        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && !isset($this->sliderCategory->SliderCategoryLanguageOne)) {
            $this->sliderCategory->SliderCategoryLanguageOne = new SliderCategoryLanguage();
            $this->sliderCategory->SliderCategoryLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }

        return $formSliderCategory;
    }
}
