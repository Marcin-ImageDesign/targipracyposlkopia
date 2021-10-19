<?php

class User_AdminParticipationController extends Engine_Controller_Admin
{
    /**
     * @var ExhibParticipationType
     */
    private $_exhibParticipationType;

    /**
     * @var ExhibParticipation
     */
    private $_exhibParticipation;

    /**
     * @var User_Form_AdminParticipation
     */
    private $_formParticipation;

    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->hasSelectedEvent()) {
            $this->setFirstSelectedEvent();
            $this->forward404Unless($this->hasSelectedEvent(), 'Event Not Selected');
        }
    }

    /**
     * List of organizators.
     */
    public function list1Action()
    {
        $this->_participationsList();
    }

    /**
     * List of exhibitors.
     */
    public function list2Action()
    {
        $this->_participationsList();
    }

    /**
     * List of moderators.
     */
    public function list6Action()
    {
        $this->_participationsList();
    }

    /**
     * List of participations.
     */
    public function list5Action()
    {
        $this->_participationsList();
    }

    /**
     * New organizator.
     */
    public function new1Action()
    {
        $this->_newParticipant();
    }

    /**
     * New exhibitor.
     */
    public function new2Action()
    {
        $this->_newParticipant();
    }

    /**
     * New moderator.
     */
    public function new6Action()
    {
        $this->_newParticipant();
    }

    /**
     * New participation.
     */
    public function new5Action()
    {
        $this->_newParticipant();
    }

    public function deleteAction()
    {
        $this->_exhibParticipation = ExhibParticipation::findOneByHash($this->_getParam('exhib_participation_hash'));
        $this->forward403Unless($this->_exhibParticipation, 'ExhibParticipation Not Found, hash:(' . $this->getParam('exhib_participation_hash') . ')');

        if (UserRole::ROLE_ADMIN !== $this->userAuth->UserRole && $this->userAuth->getId() == $this->_exhibParticipation->id_user) {
            $this->_flash->error->addMessage('You don\'t have permission to delete own participation');
            $this->_redirector->gotoRouteAndExit([], 'user_admin-participation_list-' . $this->_exhibParticipation->id_exhib_participation_type);
        }
        $this->_exhibParticipation->delete();
        $this->_flash->addMessage('Item has been deleted');
        $this->_redirector->gotoRouteAndExit([], 'user_admin-participation_list-' . $this->_exhibParticipation->id_exhib_participation_type);
    }

    public function statusAction()
    {
        $this->_exhibParticipation = ExhibParticipation::findOneByHash($this->getParam('exhib_participation_hash'));
        $this->forward403Unless($this->_exhibParticipation, 'ExhibParticipation Not Found, hash:(' . $this->getParam('exhib_participation_hash') . ')');

        $this->_exhibParticipation->is_active = !$this->_exhibParticipation->is_active;
        $this->_exhibParticipation->save();

        $this->_redirector->gotoRouteAndExit([], 'user_admin-participation_list-' . $this->_exhibParticipation->id_exhib_participation_type);
    }

    private function _participationsList()
    {
        $this->_exhibParticipationType = ExhibParticipationType::find($this->_getParam('id_exhib_participation_type'));
        $this->forward404Unless($this->_exhibParticipationType, 'ExhibParticipationType Not Found id:(' . $this->_getParam('id_exhib_participation_type') . ')');

        $participationQuery = Doctrine_Query::create()
            ->from('ExhibParticipation p')
            ->leftJoin('p.User u')
            ->where('p.id_exhib_participation_type = ?', $this->_exhibParticipationType->getId())
        ;

        if ($this->hasSelectedEvent()) {
            $participationQuery->addWhere('p.id_event = ?', $this->getSelectedEvent()->getId());
        }

        $pager = new Doctrine_Pager($participationQuery, $this->_getParam('page', 1), 20);
        $participationList = $pager->execute();
        $pagerRange = new Doctrine_Pager_Range_Sliding(['chunk' => 5], $pager);
        $pages = $pagerRange->rangeAroundPage();

        $this->view->pager = $pager;
        $this->view->pages = $pages;
        $this->view->pagerRange = $pagerRange;
        $this->view->participationList = $participationList;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_user_admin-participation_list-' . $this->_request->getActionName()));
        $this->view->action = $this->getRequest()->getActionName();
        $this->_helper->viewRenderer('participations-list');
        $this->view->exhibParticipationType = $this->_exhibParticipationType;
    }

    private function _newParticipant()
    {
        $this->_exhibParticipationType = ExhibParticipationType::find($this->_getParam('id_exhib_participation_type'));
        $this->forward404Unless($this->_exhibParticipationType, 'ExhibParticipationType Not Found id:(' . $this->_getParam('id_exhib_participation_type') . ')');

        $this->_exhibParticipation = new ExhibParticipation();
        $this->_exhibParticipation->ExhibParticipationType = $this->_exhibParticipationType;
        $this->_exhibParticipation->BaseUser = $this->getSelectedBaseUser();
        $this->_exhibParticipation->Event = $this->getSelectedEvent();
        $this->_exhibParticipation->is_active = true;
        $this->_exhibParticipation->UserCreated = $this->userAuth;
        $this->_exhibParticipation->hash = $this->engineUtils->getHash();
        $this->_formParticipation = new User_Form_AdminParticipation(
            [
                'exhibParticipation' => $this->_exhibParticipation,
                'allowChange' => false,
            ]
        );

        $this->_formParticipation();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('label_user_admin-participation_new-' . $this->_request->getActionName()));
    }

    private function _formParticipation()
    {
        if ($this->_request->isPost() && $this->_formParticipation->isValid($this->_request->getPost())) {
            $this->_exhibParticipation->save();
            $this->_flash->succes->addMessage($this->view->translate('label_form_save_success'));
            $this->_redirector->gotoRouteAndExit([], 'user_admin-participation_list-' . $this->_exhibParticipation->id_exhib_participation_type);
        }

        $this->_helper->viewRenderer('participation-form');
        $this->view->formParticipation = $this->_formParticipation;
    }
}
