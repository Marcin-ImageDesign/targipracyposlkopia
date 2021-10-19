<?php

class Event_Form_Admin_Stand_Clone extends Engine_Form
{
    /**
     * @param ExhibStand $exhibStand
     * @param array      $options
     */
    protected $_exhibStand;
    protected $_belong_to = 'EventFormAdminStandClone';
    protected $_hallMapNumbers;

    public function __construct($exhibStand, $options = null)
    {
        $fields = null;
        $this->_exhibStand = $exhibStand;

        parent::__construct($options);

        $this->setAttrib('style', 'width:450px');

        $id_event_hall_map = $this->_exhibStand->isNew() ? $this->_exhibStand->Event->id_event_hall_map : $this->_exhibStand->EventStandNumber->id_event_hall_map;

        $eventHallMapsList = Doctrine_Query::create()
            ->select('ehm.id_event_hall_map, t.name as name, ehm.uri, esn.name, esn.id_stand_level, es.id_exhib_stand, sl.name')
            ->from('EventHallMap ehm INDEXBY ehm.id_event_hall_map')
            ->leftJoin('ehm.Translations t WITH t.id_language = ? ', Engine_I18n::getLangId())
            ->innerJoin('ehm.EventStandNumbers esn INDEXBY esn.id_event_stand_number')
            ->innerJoin('esn.StandLevel sl INDEXBY sl.id_stand_level')
            ->leftJoin('esn.ExhibStand es INDEXBY esn.id_exhib_stand')
            ->where('ehm.id_event = ?', $this->_exhibStand->Event->getId())
            ->addWhere('esn.is_active = 1')
            ->addWhere('ehm.id_event_hall_map != ?', $id_event_hall_map)
            ->orderBy('esn.number')
            ->execute([], Doctrine::HYDRATE_ARRAY)
        ;

        $eventHallMapsOptions = [];
        foreach ($eventHallMapsList as $k => $v) {
            $eventHallMapsOptions[$k] = $v['name'];
            foreach ($v['EventStandNumbers'] as $kk => $vv) {
                if (!isset($standLevelNames[$vv['id_stand_level']])) {
                    $standLevelNames[$vv['id_stand_level']] = $this->getView()->translate('form_event-admin-stand_stand-level_' . $vv['StandLevel']['name']);
                }

                if (empty($vv['ExhibStand']) ||
                    $kk === $this->_exhibStand->id_event_stand_number ||
                    ExhibStand::TYPE_TEST === $this->_exhibStand->id_stand_type
                ) {
                    $this->_hallMapNumbers[$k][$vv['id_stand_level']][$kk] = $vv['name'];
                }
            }
        }

        $fields['event_hall_map'] = $this->createElement('select', 'event_hall_map', [
            'label' => 'Event Hall',
            'multiOptions' => $eventHallMapsOptions,
            'allowEmpty' => false,
            'validators' => [
                ['InArray', true, [array_keys($eventHallMapsOptions)]],
            ],
            'data-hall-map-numbers' => json_encode($this->_hallMapNumbers),
            'style' => 'width:290px',
        ]);

        $hall_ids = array_keys($eventHallMapsOptions);
        $standNumerOptions = [];
        if (array_key_exists($this->_exhibStand->id_stand_level, $this->_hallMapNumbers[$hall_ids[0]])) {
            $standNumerOptions = $this->_hallMapNumbers[$hall_ids[0]][$this->_exhibStand->id_stand_level];
        }
        $fields['id_event_stand_number'] = $this->createElement('select', 'id_event_stand_number', [
            'label' => 'Stand number',
            'multiOptions' => $standNumerOptions,
            'allowEmpty' => false,
            'style' => 'width:290px',
            'validators' => [
                ['InArray', true, [array_keys($standNumerOptions)]],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Clone',
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text small-submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons', ['class' => 'group-wrapper group-buttons group-buttons-small']);

        $this->addDisplayGroup(
            [$fields['event_hall_map'], $fields['id_event_stand_number']],
            'clone',
            [
                'class' => 'group-wrapper group-main group-small',
                'legend' => 'Clone stand',
            ]
        );
    }

    public function isValid($data)
    {
        $_data = $data[$this->getElementsBelongTo()];
        $id_stand_level = $this->_exhibStand->id_stand_level;
        $id_event_hall_map = isset($_data['event_hall_map']) ? $_data['event_hall_map'] : $this->_exhibStand->Event->id_event_hall_map;
        $multiOptions = (array) @$this->_hallMapNumbers[$id_event_hall_map][$id_stand_level];
        $standNumberField = $this->getElement('id_event_stand_number');
        $standNumberField->setMultiOptions($multiOptions);
        $inArray = $standNumberField->getValidator('InArray');
        $inArray->setHaystack(array_keys($multiOptions));

        return parent::isValid($data);
    }
}
