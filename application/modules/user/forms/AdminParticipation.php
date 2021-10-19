<?php
/**
 * User: Robert
 * Date: 14.03.13
 * Time: 09:58.
 */
class User_Form_AdminParticipation extends Engine_Form
{
    /**
     * @var ExhibParticipation
     */
    protected $_exhibParticiaption;

    /**
     * @var bool
     */
    protected $_allowChange = false;

    protected $_belong_to = 'UserFormAdminParticipation';

    protected $_tlabel = 'form_user-admin-participation_';

    /**
     * @var Doctrine_Collection
     */
    private $_usersList;

    /**
     * @var Doctrine_Collection
     */
    private $_eventsList;

    /**
     * @var Doctrine_Collection
     */
    private $_epTypeList;

    public function init()
    {
        $fields = [];

        // start definition of user field
        $usersOptions = [];

        $id_user_role = UserRole::ROLE_USER;
        if ($this->_exhibParticiaption->isOrganizer()) {
            $id_user_role = UserRole::ROLE_ORGANIZER;
        } elseif ($this->_exhibParticiaption->isExhibitor()) {
            $id_user_role = UserRole::ROLE_EXHIBITOR;
        } elseif ($this->_exhibParticiaption->isModerator()) {
            $id_user_role = UserRole::ROLE_MODERATOR;
        }

        $usersQuery = Doctrine_Query::create()
            ->from('User u INDEXBY u.hash')
            ->leftJoin('u.ExhibParticipation ep WITH ep.id_event = ?', $this->_exhibParticiaption->id_event)
            ->addWhere('u.is_active = 1 AND u.is_banned = 0')
            ->addWhere('u.id_user_role = ?', $id_user_role)
            ->addWhere('ep.id_exhib_participation IS NULL')
            ->orderBy('u.last_name ASC, u.first_name ASC')
        ;

        if (!$this->_exhibParticiaption->isNew()) {
            $usersQuery->orWhere('ep.id_exhib_participation = ?', $this->_exhibParticiaption->getId());
        }

        $this->_usersList = $usersQuery->execute();

        // @var $user User
        foreach ($this->_usersList as $hash => $user) {
            $usersOptions[$hash] = $user->getName();
        }

        $user_filed_value = null;
        if (!empty($this->_exhibParticiaption->id_user)) {
            $user_filed_value = $this->_exhibParticiaption->User->getHash();
        }

        $fields['user'] = $this->createElement(
            'select',
            'user',
            [
                'label' => $this->_tlabel . 'user',
                'required' => true,
                'multiOptions' => $usersOptions,
                'validators' => [
                    ['InArray', false, [array_keys($usersOptions)]],
                ],
                'allowEmpty' => false,
                'value' => $user_filed_value,
            ]
        );
        // end definition of user field

        // start definition of event field
        $eventsOptions = [];
        $this->_eventsList = Doctrine_Query::create()
            ->from('Event e INDEXBY e.hash')
            ->addWhere('e.is_active = 1 OR e.id_event = ?', $this->_exhibParticiaption->id_event)
            ->execute()
        ;

        foreach ($this->_eventsList as $hash => $event) {
            $eventsOptions[$hash] = $event->getTitle();
        }

        $eventFiled = $this->createElement(
            'select',
            'event',
            [
                'label' => $this->_tlabel . 'event',
                'required' => true,
                'multiOptions' => $eventsOptions,
                'disable' => true,
                'validators' => [
                    ['InArray', false, [array_keys($eventsOptions)]],
                ],
                'allowEmpty' => false,
                'value' => $this->_exhibParticiaption->Event->getHash(),
            ]
        );

        if (!$this->_allowChange) {
            $eventFiled->setRequired(false);
            $eventFiled->clearValidators();
            $eventFiled->setAllowEmpty(true);
        }

        $fields['event'] = $eventFiled;
        // end definition of event field

        // start definition of exhib participation type field
        $epTypeOptions = [];
        $this->_epTypeList = Doctrine_Query::create()
            ->from('ExhibParticipationType ept INDEXBY ept.id_exhib_participation_type')
            ->execute()
        ;

        foreach ($this->_epTypeList as $k => $v) {
            $epTypeOptions[$k] = 'form_label_user-participation_' . $v->id_exhib_participation_type;
        }

        $epTypeField = $this->createElement(
            'select',
            'epType',
            [
                'label' => $this->_tlabel . 'exhib_participation_type',
                'required' => true,
                'multiOptions' => $epTypeOptions,
                'disable' => true,
                'validators' => [
                    ['InArray', false, [array_keys($epTypeOptions)]],
                ],
                'allowEmpty' => false,
                'value' => $this->_exhibParticiaption->id_exhib_participation_type,
            ]
        );

        if (!$this->_allowChange) {
            $epTypeField->setRequired(false);
            $epTypeField->clearValidators();
            $epTypeField->setAllowEmpty(true);
        }

        $fields['epType'] = $epTypeField;
        // end definition of exhib participation type field

        $listOptionsNoYes = ['0' => 'label_form_no', '1' => 'label_form_yes'];
        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => (int) $this->_exhibParticiaption->is_active,
            'validators' => [
                ['InArray', false, [array_keys($listOptionsNoYes)]],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');

        $this->addDisplayGroup(
            $fields,
            'main',
            ['legend' => $this->_tlabel . 'group_main']
        );

        $this->addDisplayGroup(
            [$is_active],
            'aside',
            ['legend' => $this->_tlabel . 'group_aside']
        );
    }

    protected function setExhibParticipation($exhibParticipation)
    {
        $this->_exhibParticiaption = $exhibParticipation;
    }

    protected function setAllowChange($allowChange)
    {
        $this->_allowChange = $allowChange;
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->getElementsBelongTo()];

        $this->_exhibParticiaption->User = $this->_usersList[$data['user']];

        if (!empty($data['event'])) {
            $this->_exhibParticiaption->Event = $this->_eventsList[$data['event']];
        }

        if (!empty($data['epType'])) {
            $this->_exhibParticiaption->ExhibParticipationType = $this->_epTypeList[$data['epType']];
        }

        return true;
    }
}
