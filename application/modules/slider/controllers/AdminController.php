<?php

class Slider_AdminController extends Engine_Controller_Admin
{
    /**
     * @var \Slider_Form_Filter|mixed
     */
    public $filter;
    /**
     * @var \Doctrine_Query|mixed
     */
    public $sliderListQuery;
    /**
     * @var Slider
     */
    private $slider;

    /**
     * @var SliderCategory
     */
    private $sliderCategory;

    /**
     * @var Slider_Form_Element
     */
    private $formSlider;
    private $is_category = 1;

    /**
     * @var Doctrine_Collection
     */
    private $sliderList;

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu slider
            $this->view->renderToPlaceholder('admin/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        //$category_hash = $this->_hasParam('hash');
        if (1 === $this->is_category) {
            $this->sliderCategory = SliderCategory::findOneByHash($this->_getParam('hash'));
            $this->forward404Unless($this->sliderCategory, 'Slider category NOT Exists (' . $this->_getParam('hash') . ')');
            //sprawdzenie praw do elementu
            $this->forward403Unless(
                $this->getSelectedBaseUser()->hasAccess($this->sliderCategory),
                [$this->getSelectedBaseUser()->getId(), $this->sliderCategory->getId()]
            );
        }
        // Ustawienie sortowania listy
        //Deklaracja tablicy z filtrami
        $search = ['title' => '', 'is_active' => ''];

        if ($this->_hasParam('clear')) {
            //czyszczenie filtra
            $this->_session->__unset('slider_filter');
        } else {
            //Ustawienie filtrów z wyszukiwarki
            foreach ($search as $key => $value) {
                $search[$key] = $this->_session->slider_filter->{$key} = $this->_hasParam($key) ? trim($this->_getParam($key, $value)) : (isset($this->_session->slider_filter->{$key}) ? $this->_session->slider_filter->{$key} : $value);
            }
        }
        // deklaracja formularza slider/form/Filter.php
        $this->filter = new Slider_Form_Filter();
        $this->filter->populate($search);

        //tworzenie zaptania zwracającego listę
        $this->sliderListQuery = Doctrine_Query::create()
            ->from('Slider s')
            ->where('s.id_base_user = ?', $this->getSelectedBaseUser()->getId())
            ->orderBy('s.order desc')
    ;

        if (1 === $this->is_category) {
            $this->sliderListQuery->addWhere('s.id_slider_category = ?', $this->sliderCategory->getId());
        } else {
            $this->sliderListQuery->addWhere('s.id_slider_category IS NULL');
        }
        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && isset($this->getSelectedLanguage()->BaseUserLanguageOne)) {
            $this->sliderListQuery->leftJoin('s.SliderLanguageOne slo WITH slo.id_base_user_language = ?', $this->getSelectedLanguage()->BaseUserLanguageOne->getId());
        }
        //sprawdzenie tytułu z filtra
        if (!empty($search['title'])) {
            $this->sliderListQuery->addWhere('s.title LIKE ?', '%' . $search['title'] . '%');
        }
        //sprawdzenie statusu z filtra
        if (mb_strlen($search['is_active']) > 0) {
            $this->sliderListQuery->addWhere('s.is_active = ?', $search['is_active']);
        }

        //paginacja - przekazujemy: wygenerowany query z listą wynikow, numer strony, ilość wyników na stronie
        $sliderList = $this->sliderListQuery->execute();

        $this->view->sliderList = $sliderList;
        $this->view->filter = $this->filter;

        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set('Slider Manager');
        $this->view->sliderCategory = $this->sliderCategory;
    }

    //Zmiana statusu (aktywny/nie aktywny) na podstronie listy.
    public function statusAction()
    {
        //pobranie elemntu po polu hash
        $this->slider = Slider::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy dany element istnieje
        $this->forward404Unless($this->slider, 'Slider NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->slider),
            [$this->getSelectedBaseUser()->getId(), $this->slider->getId()]
        );
        //zmiana statusu
        $this->slider->is_active = !(bool) $this->slider->is_active;
        //zapis do bazy
        $this->slider->save();
        //przekierowanie na podstronę listy
        $this->sliderCategory = $this->slider->SliderCategory;
        if ($this->sliderCategory) {
            $this->_redirector->gotoRouteAndExit(['hash' => $this->sliderCategory->getHash()], 'admin_slider');
        } else {
            $this->_redirector->gotoRouteAndExit([], 'admin_slider');
        }
    }

    public function moveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sliderMove = Slider::findOneByHash($this->_getParam('hash_element_move'));
        //sprawdzenie czy dany element istnieje
        $this->forward404Unless($sliderMove, 'Slider NOT Exists (' . $this->_getParam('hash_element_move') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($sliderMove),
            [$this->getSelectedBaseUser()->getId(), $sliderMove->getId()]
        );
        $order_move = $sliderMove->getOrder();
        $slider = Slider::findOneByHash($this->_getParam('hash_element'));
        $this->forward404Unless($slider, 'Slider NOT Exists (' . $this->_getParam('hash_element') . ')');
        //sprawdzenie praw do elementu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($slider),
            [$this->getSelectedBaseUser()->getId(), $slider->getId()]
        );
        $order = $slider->getOrder();
        if ('after' === $this->_getParam('type')) {
            if ($order_move > $order) {
                $sliderMove->setOrder($order);
            } else {
                $sliderMove->setOrder($order - 1);
            }
        } elseif ($order_move > $order) {
            $sliderMove->setOrder($order + 1);
        } else {
            $sliderMove->setOrder($order);
        }

        $sliderListQuery = Doctrine_Query::create()
            ->from('Slider s')
            ->where('s.id_base_user = ?', $slider->id_base_user)
    ;

        $sliderListQuery->addWhere('s.id_slider_category = ?', $slider->id_slider_category);
        if ($order_move > $order) {
            if ('before' === $this->_getParam('type')) {
                $sliderListQuery->addWhere('s.order > ?', $order);
                $sliderListQuery->addWhere('s.order < ?', $order_move);
            } else {
                $sliderListQuery->addWhere('s.order > ?', $order - 1);
                $sliderListQuery->addWhere('s.order < ?', $order_move);
            }
        } elseif ('before' === $this->_getParam('type')) {
            $sliderListQuery->addWhere('s.order > ?', $order_move);
            $sliderListQuery->addWhere('s.order < ?', $order + 1);
        } else {
            $sliderListQuery->addWhere('s.order > ?', $order_move);
            $sliderListQuery->addWhere('s.order < ?', $order);
        }

        //	$upadateQuery = Doctrine_Query::create()
        //		->update('Slider s')
        //		->where('s.id_base_user = ?', $slider->id_base_user);
//
        //	$upadateQuery->addWhere('s.id_slider_category = ?', $slider->id_slider_category);
//
        //	if ($order_move > $order) {
        //	    if ($this->_getParam('type') == 'before') {
        //		$upadateQuery->addWhere('s.order > ?', $order);
        //		$upadateQuery->addWhere('s.order < ?', $order_move);
        //	    } else {
        //		$upadateQuery->addWhere('s.order > ?', $order - 1);
        //		$upadateQuery->addWhere('s.order < ?', $order_move);
        //	    }
        //	    $upadateQuery->set('s.order', 's.order+1');
        //	} else {
        //	    if ($this->_getParam('type') == 'before') {
        //		$upadateQuery->addWhere('s.order > ?', $order_move);
        //		$upadateQuery->addWhere('s.order < ?', $order + 1);
        //	    } else {
        //		$upadateQuery->addWhere('s.order > ?', $order_move);
        //		$upadateQuery->addWhere('s.order < ?', $order);
        //	    }
        //	    $upadateQuery->set('s.order', 's.order-1');
        //	}
//
//
//
//
        //	$upadateQuery->execute();

        $sliderList = $sliderListQuery->execute();
        //var_dump($sliderList->toArray(1));die();
        foreach ($sliderList as $value) {
            if ($order_move > $order) {
                $value->setOrder($value->getOrder() + 1);
            } else {
                $value->setOrder($value->getOrder() - 1);
            }

            $value->save();
        }

        $sliderMove->save();
    }

    //dodanie nowego elementu
    public function newAction()
    {
        if (1 === $this->is_category) {
            $this->sliderCategory = SliderCategory::findOneByHash($this->_getParam('hash'));
            $this->forward404Unless($this->sliderCategory, 'Slider category NOT Exists (' . $this->_getParam('hash') . ')');
            //sprawdzenie praw do elementu
            $this->forward403Unless(
                $this->getSelectedBaseUser()->hasAccess($this->sliderCategory),
                [$this->getSelectedBaseUser()->getId(), $this->sliderCategory->getId()]
            );
        }
        //tworzenie nowej instancji
        $engineUtils = Engine_Utils::getInstance();
        //tworzenie nowego elementu
        $this->slider = new Slider();
        //pobieranie id klienta
        $this->slider->BaseUser = $this->getSelectedBaseUser();

        if (null !== $this->sliderCategory) {
            $this->slider->SliderCategory = $this->sliderCategory;
        }
        $this->setSelectedLanguage();
        //pobranie tymczasowego hash-a
        $this->slider->hash = $engineUtils->getHash();

        //uruchomienie prywatnej funkcji generującej formularz
        $this->formSlider();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set($this->view->translate('New slider'));
    }

    //edycja istniejącego elementu
    public function editAction()
    {
        //pobranie elementu po polu hash
        $this->slider = Slider::findOneByHash($this->_getParam('hash'), $this->getSelectedLanguage());
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->slider, 'Slider NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->slider),
            [$this->getSelectedBaseUser()->getId(), $this->slider->getId()]
        );

        //uruchomienie prywatnej funkcji generującej formularz
        $this->formSlider();
        //nagłówek podstrony
        $this->view->placeholder('headling_1_content')->set('Edycja slider');
    }

    //usuwanie zdjęcia z podstrony edycji
    public function deleteImgAction()
    {
        //pobranie obiektu na podstawie pola hash
        $this->slider = Slider::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->slider, 'Slider Not Found hash: (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->slider),
            [$this->getSelectedBaseUser()->getId(), $this->slider->getId()]
        );
        //Usuwanie zdjęcia z serwera
        $this->slider->deleteImage();
        //zapis do bazy
        $this->slider->save();
        $this->_flash->success->addMessage($this->view->translate('Photo deleted'));
        //przekierowanie na podstronę edycji
        $this->_redirector->gotoRouteAndExit(['hash' => $this->slider->getHash()], 'admin_slider_edit');
    }

    //usuwanie slidera
    public function deleteAction()
    {
        //pobranie obiektu po polu hash
        $this->slider = Slider::findOneByHash($this->_getParam('hash'));
        //sprawdzenie czy istnieje
        $this->forward404Unless($this->slider, 'Slider NOT Exists (' . $this->_getParam('hash') . ')');
        //sprawdzenie czy są prawa dostępu
        $this->forward403Unless(
            $this->getSelectedBaseUser()->hasAccess($this->slider),
            [$this->getSelectedBaseUser()->getId(), $this->slider->getId()]
        );
        //usuwanie całego obiektu
        $this->sliderCategory = $this->slider->SliderCategory;
        $this->slider->delete();

        $this->_flash->success->addMessage($this->view->translate('Slider deleted'));
        //Przekierowanie na podstronę listy

        if ($this->sliderCategory) {
            $this->_redirector->gotoRouteAndExit(['hash' => $this->sliderCategory->getHash()], 'admin_slider');
        } else {
            $this->_redirector->gotoRouteAndExit([], 'admin_slider');
        }
    }

    private function formSlider()
    {
        //pobranie szablonu
        $this->_helper->viewRenderer('form-slider');
        //pobranie formularza dla pojedyńczego elementu
        $this->formSlider = $this->getFormSlider();

        //Sprawdzenie próby zapisu formularza
        //sprawdzenie poprawności danych w polach formularza
        if ($this->_request->isPost() && $this->formSlider->isValid($this->_request->getPost())) {
            //Przetworzenie zmiennych w formularzu
            $this->sliderGetRequest();
            //zapis zmian do bazy
            $this->slider->save();
            //Tworzenie informacji zwrotnej
            $this->_flash->success->addMessage($this->view->translate('Slider saved'));
            //Przekierowanie na podstronę edycji
            $this->_redirector->gotoRouteAndExit(['hash' => $this->slider->hash], 'admin_slider_edit');
        }

        //Wysyłanie zmiennych do szablonu
        $this->view->formSlider = $this->formSlider;
        $this->view->slider = $this->slider;
        //nagłówek podstrony
        $this->view->placeholder('section-class')->set('tpl-form');
        $this->view->sliderCategory = $this->slider->SliderCategory;
    }

    private function sliderGetRequest()
    {
        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && !isset($this->slider->SliderLanguageOne)) {
            $this->slider->SliderLanguageOne = new SliderLanguage();
            $this->slider->SliderLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }
        //sprawdzenie czy została wysłana grafika
        $information = '';
        if (!empty($_FILES['img']['name'])) {
            //zamiana nazwy plik na tablicę gdzie separatorem jest kropka
            $file_tmp = explode('.', $_FILES['img']['name']);
            //tworzenie nazwy dla pliku (hash + rozszerzenie grafiki np. jpg,png lub gif)
            $file_name = $this->slider->getHash() . '.' . $file_tmp[count($file_tmp) - 1];
            //Gdy element posiada już grafikę wywołujemy funkcję która ją usunie
            $this->slider->deleteImage();
            //Przypisanie do pola grafiki w formularzu nowej nazwy pliku
            $this->formSlider->main->img->addFilter(new Zend_Filter_File_Rename(['target' => $file_name]));

            //Próba zapisu pliku na serwerze
        if (!$this->formSlider->main->img->receive()) { // Odbiór pliku
        $information = 'Błąd podczas odbierania pliku.';
        }
            //Przypisanie do obiektu slider nowej nazwy grafiki
            $this->slider->setImg($file_name);
        }
        $this->view->error_img = $information;
        //Przypisanie pol z formularza do elemntów obiektu slider
        $this->slider->is_active = (bool) $this->formSlider->aside->is_active->getValue();
        $this->slider->setTitle($this->formSlider->header->title->getValue());
        $this->slider->setUrl($this->formSlider->main->url->getValue());
        $this->slider->setDescription($this->formSlider->main->description->getValue());
        if ($this->slider->isNew()) {
            $this->slider->order = $this->slider->getLastOrder($this->slider->getCategoryId()) + 1;
        }
    }

    /**
     * @return Slider_Form_Element
     */
    private function getFormSlider()
    {
        $params = null;
        //Pobranie formularza slider/from/Element.php
        $formSlider = new Slider_Form_Element($this->slider);
        //Jeżeli folder nie istnieje, zostanie utworzony
        if (!file_exists($this->slider->getAbsolutePath())) {
            $utils = Engine_Utils::getInstance();
            $utils->createDir($this->slider->getAbsolutePath());
        }
        //Przypisanie img scieżki gdzie mają zostać zapisywane pliki
        $formSlider->main->img->setDestination($this->slider->getAbsolutePath());
        //Tworzenie tablicy z elemntów formularza
        $formSlider->populate($this->slider->getArrayToForm());
        //sprawdzenie języka
        if (Language::DEFAULT_LANGUAGE_CODE !== $this->getSelectedLanguage()->code && !isset($this->slider->SliderLanguageOne)) {
            $this->slider->SliderLanguageOne = new SliderLanguage();
            $this->slider->SliderLanguageOne->BaseUserLanguage = $this->getSelectedLanguage()->BaseUserLanguageOne;
        }

        //sprawdzenie czy mamy doczynienia z nowym elementem czy edycją już istniejącego
        if (false === $this->slider->isNew()) {
            $params[] = ['id_slider', '!=', $this->slider->getId()];
        }

        //ustawienie zmiennych odpowiedzilanych za język
        if (isset($this->slider->SliderLanguageOne)) {
            $model = 'SliderLanguage';
            $params[] = ['id_base_user_language', '=', $this->slider->SliderLanguageOne->id_base_user_language];
        } else {
            $model = 'Slider';
            $params[] = ['id_base_user', '=', $this->getSelectedBaseUser()->getId()];
        }
        //budowa validatora uniemożliwiającego zapisanie slidera o tytule który już istnieje
        //$vAlreadyTaken = new Engine_Validate_AlreadyTaken($model, 'title', $params);
        //Dodanie walidatora do pola title w fomularzu
        //$formSlider->getSubForm('header')->getElement('title')->addValidator($vAlreadyTaken);

        return $formSlider;
    }
}
