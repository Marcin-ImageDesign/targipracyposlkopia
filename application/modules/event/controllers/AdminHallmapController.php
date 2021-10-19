<?php

class Event_AdminHallmapController extends Engine_Controller_Admin
{
    /**
     * @var int
     */
    private $eventId;

    private $_hallmap;

    private $_hallmapForm;

    public function postDispatch()
    {
        parent::postDispatch();
        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-hallmap/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        if (!$this->userAuth->isAdmin() && !$this->getSelectedEvent()) {
            $url = $this->view->url([], 'admin_select-event') . '?return=' . $this->view->url();
            $this->_redirector->gotoUrlAndExit($url);
        }

        $eventHallMapsQuery = Doctrine_Query::create()
            ->from('EventHallMap eh')
            ->leftJoin('eh.Translations t')
            ->leftJoin('eh.EventStandNumbers esn')
            ->leftJoin('eh.Event e')
            ->orderBy('t.name ASC')
        ;

        if ($this->getSelectedEvent()) {
            $eventHallMapsQuery->where('eh.id_event = ?', $this->getSelectedEvent()->getId());
        }

        $pager = new Doctrine_Pager($eventHallMapsQuery, $this->_getParam('page', 1), 20);
        $eventHallMapsList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();
        $this->view->eventHallMapsList = $eventHallMapsList;
        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_hallmap-list'));
    }

    public function hallMapEditAction()
    {
    }

    public function newAction()
    {
        $this->_hallmap = new EventHallMap();
        $this->_hallmap->hash = $this->engineUtils->getHash();

        // formularz edycji
        $this->_hallmapForm = new Event_Form_Admin_Hallmap_New(['model' => $this->_hallmap, 'event' => $this->getSelectedEvent()]);

        if ($this->_request->isPost() && $this->_hallmapForm->isValid($this->_request->getPost())) {
            // ustawiamy name
            $this->_hallmap->setName($this->_hallmapForm->name->getValue());
            //wyciągamy szablon do klonowania
            $base = EventHallMap::findTemplateByUri($this->_hallmapForm->hall_template->getValue());
            //klonujemy halę
            $this->_hallmap->id_image = $base->getIdImage();
            $this->_hallmap->height = $base->getHeight();
            $this->_hallmap->width = $base->getWidth();
            $this->_hallmap->hall_map = '{"attribs":{"id":"mymap","name":"mymap"},"items":null}';
            $this->_hallmap->uri = Engine_Utils::_()->getFriendlyUri($this->_hallmapForm->name->getValue());
            $this->_hallmap->is_template = 0;
            $this->_hallmap->id_event = $this->getSelectedEvent()->getId();
            // EventStandNumber
            foreach ($base->EventStandNumbers as $standNumber) {
                $clone = new EventStandNumber();
                $clone->id_event_hall_map = $this->_hallmap->getId();
                $clone->id_stand_level = $standNumber->getIdStandLevel();
                $clone->hash = $this->engineUtils->getHash();
                $clone->is_active = 1;
                $clone->number = $standNumber->getNumber();
                $clone->name = $standNumber->getName();
                $clone->logo_pos_x = $standNumber->getLogoPosX();
                $clone->logo_pos_y = $standNumber->getLogoPosY();
                $clone->map_cords = $standNumber->getMapCords();
                $this->_hallmap->EventStandNumbers->add($clone);
            }
            $this->_hallmap->save();
            if ($this->_hallmap->getId() !== 0) {
                $this->_flash->success->addMessage($this->view->translate('Hall successfully added!'));
                $this->_redirector->gotoRouteAndExit(['hallmap_hash' => $this->_hallmap->getHash()], 'event_admin-hallmap_edit');
            } else {
                $this->_flash->error->addMessage($this->view->translate('Adding hall failed!'));
                $this->_redirector->gotoRouteAndExit([], 'event_admin-hallmap_new');
            }
        }
        $this->view->formHallmap = $this->_hallmapForm;
    }

    public function editAction()
    {
        $this->_hallmap = EventHallMap::findOneByHash($this->_getParam('hallmap_hash'));
        // formularz edycji
        $this->_hallmapForm = new Event_Form_Admin_Hallmap_Edit(['model' => $this->_hallmap]);

        $this->hallmapForm();
    }

    public function getBannerAction()
    {
        $banner = new Event_Form_Admin_Hallmap_Banner([
            'data' => [],
            'key' => 'new',
        ]);

        $hall_map_hash_session = new Zend_Session_Namespace('hall_map_hash_session');

        if (!empty($hall_map_hash_session->hash)) {
            // pobieram z sesji hash mapy i odczytuje najwyższy klucz banerku i zapisuje go do sesji
            $hallmap = EventHallMap::findOneByHash($hall_map_hash_session->hash)->getHallMap();
            $hallmapKey = [];
            foreach (array_keys($hallmap['items']) as $key) {
                $hallmapKey[] = $key;
            }
            $countBanners = max($hallmapKey);
            unset($hall_map_hash_session->hash);
            $countBanners = $hall_map_hash_session->countBanners = ++$countBanners;
        // jeśli było już raz odczytywane to dodaj jeden i przypisz ponownie
        } elseif (!empty($hall_map_hash_session->countBanners)) {
            $countBanners = $hall_map_hash_session->countBanners = ++$hall_map_hash_session->countBanners;
        // jeśli nowa hala bez banerków to przypisz jedynkę
        } else {
            unset($hall_map_hash_session->hash);
            $countBanners = $hall_map_hash_session->countBanners = 1;
        }

        $banner->setElementsBelongTo('EventFormAdminHallmap[hall_map][' . $countBanners . ']');

        $this->jsonResult['result'] = true;
        $this->jsonResult['html'] = $banner->render();
    }

    public function assignAction()
    {
        $this->_hallmap = EventHallMap::findOneByHash($this->_getParam('hallmap_hash'));

        $this->getSelectedEvent()->EventHallMap = $this->_hallmap;

        $this->getSelectedEvent()->save();

        $this->_flash->succes->addMessage($this->view->translate('message_success_save'));

        $this->_redirector->gotoRouteAndExit([], 'event_admin-hallmaps');
    }

    public function deleteAction()
    {
        $this->_hallmap = EventHallMap::findOneByHash($this->_getParam('hallmap_hash'));
        $this->forward403Unless($this->_hallmap, 'Hall Map not found hash: (' . $this->_getParam('hallmap_hash') . ')');

        if (!$this->_hallmap->hasEvent()) {
            $this->_hallmap->delete();
            $this->_flash->succes->addMessage($this->view->translate('message_delete_save'));
        } else {
            $this->_flash->error->addMessage($this->view->translate('message_hallmap_assigned'));
        }
        $this->_redirector->gotoRouteAndExit([], 'event_admin-hallmaps');
    }

    private function hallmapForm()
    {
        if ($this->_request->isPost() && $this->_hallmapForm->isValid($this->_request->getPost())) {
            $this->_hallmap->save();
            if (isset($_FILES['image']) && 0 === $_FILES['image']['error']) {
                $image = Service_Image::createImage(
                    $this->_hallmap,
                    [
                        'type' => $_FILES['image']['type'],
                        'name' => $_FILES['image']['name'],
                        'source' => $_FILES['image']['tmp_name'], ]
                );

                // obrazek wgrany -> sprawdzenie i zapis wymiarów do bazy
                $size = getimagesize(ROOT_PATH . $image->file_path);
                $this->_hallmap->setWidth($size[0]);
                $this->_hallmap->setHeight($size[1]);

                $this->_hallmap->setImage($image->getId());
                $this->_hallmap->save();
            }
            //promo image
            if (isset($_FILES['promo_image']) && 0 === $_FILES['promo_image']['error']) {
                $image = Service_Image::createImage(
                    $this->_hallmap,
                    [
                        'type' => $_FILES['promo_image']['type'],
                        'name' => $_FILES['promo_image']['name'],
                        'source' => $_FILES['promo_image']['tmp_name'], ]
                );

                $this->_hallmap->setIdPromoPhoto($image->getId());
                $this->_hallmap->save();
            }
            $this->_flash->succes->addMessage($this->view->translate('message_success_save'));
            $this->_redirector->gotoRouteAndExit(
                ['hallmap_hash' => $this->_hallmap->getHash()],
                'event_admin-hallmap_edit'
            );
        }

        //echo $this->_hallmap->getHash();
        $hall_map_hash_session = new Zend_Session_Namespace('hall_map_hash_session');
        $hall_map_hash_session->hash = $this->_hallmap->getHash();

        $this->view->formHallmap = $this->_hallmapForm;
    }
}
