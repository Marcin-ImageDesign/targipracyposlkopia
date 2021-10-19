<?php

class Event_AdminBannerManagementController extends Engine_Controller_Admin
{
    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('admin-banner-management/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        if (!$this->getSelectedEvent()) {
            $url = $this->view->url([], 'admin_select-event') . '?return=' . $this->view->url();
            $this->_redirector->gotoUrlAndExit($url);
        }

        $group = $this->_getParam('group');
        $groupList = EventBannerGroup::getGroups();
        if (empty($group) || !in_array($group, $groupList, true)) {
            $group = EventBannerGroup::BANNER_DEFAULT_GROUP;
        }
        $bannerManagementForm = new Event_Form_Admin_GroupManager(['event' => $this->getSelectedEvent(),
            'group' => $group, ]);

        if ($this->_request->isPost() && $bannerManagementForm->isValid($this->_request->getPost())) {
            $this->_flash->success->addMessage('Save successfully completed');
            $this->_redirector->gotoRouteAndExit([], 'admin_banner_management');
        }

        $this->view->event = $this->getSelectedEvent();
        $this->view->bannerManagementForm = $bannerManagementForm;
        $this->view->group = $group;
        $this->view->groupList = $groupList;
    }

    public function getBannerAction()
    {
        $group = $this->getParam('group');
        $key = time();
        $banner = new Event_Form_Admin_GroupManagerBanner([
            'data' => ['name' => '', 'link' => '', 'target' => '', 'image' . $key => '', 'style' => '', 'image' => ''],
            'key' => $key,
        ]);

        $banner->setElementsBelongTo('EventFormAdminGroupManager[' . $group . '][' . $key . ']');

        $this->jsonResult['result'] = true;
        $this->jsonResult['html'] = $banner->render();
    }
}
