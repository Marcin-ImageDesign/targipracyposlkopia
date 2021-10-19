<?php

class Event_Form_Admin_Stand_Edit extends Event_Form_Admin_Stand
{
    /**
     * @param ExhibStand $exhibStand
     * @param array      $options
     * @param mixed      $language
     * @param mixed      $userAuth
     */
    public function __construct($exhibStand, $language, $userAuth, $options = null)
    {
        $this->_exhibStand = $exhibStand;
        $this->_language = $language;
        $this->_userAuth = $userAuth;

        parent::__construct($options);
        $this->addBrandGroup();

        $gMainAdv = $this->getDisplayGroup('main-adv');

        $eventField = $gMainAdv->getElement('id_event');
        $eventField->setAttrib('disable', true);
        $eventField->setAllowEmpty(true);

        $concat_participation_name = !empty($this->_exhibStand->id_event) ? "CONCAT(u.last_name, ' ', u.first_name), u.company, u.email" : "CONCAT(u.last_name, ' ', u.first_name), u.company, u.email, e.title";
        $participationQuery = Doctrine_Query::create()
            ->select("p.hash, CONCAT_WS(', ',{$concat_participation_name}) as participation_name")
            ->from('ExhibParticipation p')
            ->leftJoin('p.User u')
            ->leftJoin('p.Event e')
            ->addWhere('p.is_active = 1 AND p.id_exhib_participation_type IN (?) AND p.id_base_user = ?', [ExhibParticipationType::TYPE_EXHIBITOR, $this->_exhibStand->id_base_user])
            ->addWhere('p.id_event = ? ', [$this->_exhibStand->id_event])
        ;

        $participationListOptions = $participationQuery->execute()
            ->toKeyValueArray('hash', 'participation_name')
        ;

        $ExhibStandParticipationsHashes = [];
        if (!empty($this->_exhibStand)) {
            $ExhibStand = ExhibStand::findOneByHash($this->_exhibStand->getHash(), $this->baseUser);
            if (!empty($ExhibStand->ExhibStandParticipation)) {
                $ExhibStandParticipations = $ExhibStand->ExhibStandParticipation;
                foreach ($ExhibStandParticipations as $ExhibStandParticipation) {
                    if ($ExhibStandParticipation->is_active) {
                        $ExhibStandParticipationsHashes[] = $ExhibStandParticipation->ExhibParticipation->getHash();
                    }
                }
            }
        }

        $participation = $this->createElement('multiselect', 'participation', [
            'label' => $this->_tlabel . 'participation',
            'multiOptions' => $participationListOptions,
            'allowEmpty' => false,
            'required' => true,
            'value' => $ExhibStandParticipationsHashes,
            'class' => 'multiselect',
            'style' => 'width:610px; height: 200px;',
            'validators' => [
                ['InArray',
                    true,
                    [array_keys($participationListOptions)], ],
            ],
        ]);

        $vOnlyOneEvent = new Private_Validate_OnlyOneEvent();
        //Dodanie walidatora do pola title w fomularzu
        $participation->addValidator($vOnlyOneEvent);

        $decorator = $participation->getDecorator('data');
        $decorator->setOption('style', 'width: 601px;margin-top:10px;');

        $this->addElement($participation);
        $gMainAdv->addElement($participation);
    }
}
